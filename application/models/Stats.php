<?php

class Stats extends MyAppModel
{

    public const SELLER_DASHBOARD_SALES_MONTH = 6;
    public const COMPLETED_SALES = 1;
    public const INPROCESS_SALES = 2;
    public const REFUNDED_SALES = 3;
    public const CANCELLED_SALES = 4;
    
    public static function getSalesStatsObj($startDate = false, $endDate = false, $alias = 'stats', $type = self::COMPLETED_SALES, int $opProductType = 0)
    {
        $srch = new SearchBase(Orders::DB_TBL_ORDER_PRODUCTS, $alias);
        $srch->joinTable(OrderProductData::DB_TBL, 'LEFT OUTER JOIN', $alias . '.op_id = ' . $alias . 'data.opd_op_id', $alias . 'data');
        $srch->joinTable(Orders::DB_TBL, 'LEFT OUTER JOIN', $alias . '.op_order_id = ' . $alias . 'temp.order_id', $alias . 'temp');
        $srch->joinTable(Plugin::DB_TBL, 'LEFT OUTER JOIN', $alias . 'temp.order_pmethod_id = ' . $alias . 'pm.plugin_id', $alias . 'pm');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        if ($opProductType > 0) {
            $srch->addCondition('opd_sold_or_rented', '=', $opProductType);
        }
        
        if ($startDate) {
            $srch->addCondition($alias . 'temp.order_date_added', '>=', $startDate . ' 00:00:00');
        }

        if ($endDate) {
            $srch->addCondition($alias . 'temp.order_date_added', '<=', $endDate . ' 23:59:59');
        }
        $completedOrderStatus = unserialize(FatApp::getConfig("CONF_COMPLETED_ORDER_STATUS", FatUtility::VAR_STRING, ''));
        switch ($type) {
            case self::COMPLETED_SALES:
                if (!empty($completedOrderStatus)) {
                    $srch->addCondition($alias . '.op_status_id', 'IN', $completedOrderStatus);
                }
                break;
            case self::INPROCESS_SALES:
                if (!empty($completedOrderStatus)) {
                    $srch->addCondition($alias . '.op_status_id', 'NOT IN', $completedOrderStatus);
                }
                $srch->addCondition($alias . '.op_status_id', '!=', FatApp::getConfig('CONF_DEFAULT_ORDER_STATUS'));
                break;
            case self::REFUNDED_SALES:
                $srch->addCondition($alias . '.op_status_id', '=', FatApp::getConfig('CONF_RETURN_REQUEST_APPROVED_ORDER_STATUS'));
                break;
            case self::CANCELLED_SALES:
                $srch->addCondition($alias . '.op_status_id', '=', FatApp::getConfig('CONF_DEFAULT_CANCEL_ORDER_STATUS'));
                break;
        }

        $cnd = $srch->addCondition($alias . 'temp.order_payment_status', '=', Orders::ORDER_PAYMENT_PAID);
        $cnd->attachCondition($alias . 'pm.plugin_code', '=', 'CashOnDelivery');

        return $srch;
    }

    public static function getTotalSalesStats($startDate = false, $endDate = false, $alias = 'stats')
    {
        $srch = Stats::getSalesStatsObj($startDate, $endDate, $alias);
        $srch->joinTable(OrderProduct::DB_TBL_CHARGES, 'LEFT OUTER JOIN', $alias . 'c.opcharge_op_id = ' . $alias . '.op_id and ' . $alias . 'c.opcharge_type = ' . OrderProduct::CHARGE_TYPE_SHIPPING, $alias . 'c');
        $srch->addMultipleFields(array("count(" . $alias . ".op_id) as " . $alias . 'Count', 'SUM(((' . $alias . '.op_unit_price *' . $alias . '.op_qty ) + IFNULL(' . $alias . 'c.opcharge_amount,0)) - ' . $alias . '.op_refund_amount) AS ' . $alias . 'Sales'));
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        return $row;
    }

    public static function getUserSales($userId, $months = 12)
    {
        $userId = FatUtility::int($userId);
        $months = FatUtility::int($months);
        if (1 > $userId) {
            trigger_error(Labels::getLabel('MSG_INVALID_REQUEST', CommonHelper::getLangId()), E_USER_ERROR);
            return false;
        }
        $last12Months = self::getLast12MonthsDetails($months);


        foreach ($last12Months as $key => $val) {
            $completedOrderStatus = unserialize(FatApp::getConfig("CONF_COMPLETED_ORDER_STATUS"));
            if (!empty($completedOrderStatus)) {
                $completedOrderStatus = implode(",", unserialize(FatApp::getConfig("CONF_COMPLETED_ORDER_STATUS")));
            } else {
                $completedOrderStatus = 0;
            }

            $rsSales = FatApp::getDb()->query("SELECT SUM((op_unit_price*op_qty) + COALESCE(opcharge_amount,0) - op_refund_amount) AS Sales FROM `tbl_order_products` t1 LEFT OUTER JOIN tbl_order_product_charges opc on opc.opcharge_op_id = t1.op_id and opc.opcharge_type = " . OrderProduct::CHARGE_TYPE_SHIPPING . "   LEFT OUTER JOIN tbl_order_products_data opd on opd.opd_op_id = t1.op_id  INNER JOIN tbl_orders t2 on t1.op_order_id=t2.order_id INNER JOIN tbl_shops ts on ts.shop_id=t1.op_shop_id  WHERE t2.order_payment_status = 1 and t1.op_status_id in (" . $completedOrderStatus . ") and month( t2.`order_date_added` )= $val[monthCount] and year( t2.`order_date_added` )= $val[year] and ts.shop_user_id=" . (int) $userId .' AND opd.opd_sold_or_rented ='. applicationConstants::PRODUCT_FOR_SALE);

            $row = FatApp::getDb()->fetch($rsSales);

            $sales_arr[$val['monthShort'] . "-" . $val['yearShort']] = $row["Sales"];
        }
        return $sales_arr;
    }

    public static function getLast12MonthsDetails($months = 12)
    {
        $month = date('m');
        $year = date('Y');
        $i = 1;
        $date = array();

        while ($i <= $months) {
            $timestamp = mktime(0, 0, 0, $month, 1, $year);
            $date[$i]['monthCount'] = date('m', $timestamp);
            $date[$i]['monthShort'] = date('M', $timestamp);
            $date[$i]['yearShort'] = date('y', $timestamp);
            $date[$i]['year'] = date('Y', $timestamp);
            $month--;
            $i++;
        }
        return $date;
    }
    
    public static function getUserRental($userId, $months = 12)
    {
        $userId = FatUtility::int($userId);
        $months = FatUtility::int($months);
        if (1 > $userId) {
            trigger_error(Labels::getLabel('MSG_INVALID_REQUEST', CommonHelper::getLangId()), E_USER_ERROR);
            return false;
        }
        $last12Months = self::getLast12MonthsDetails($months);


        foreach ($last12Months as $key => $val) {
            $completedOrderStatus = unserialize(FatApp::getConfig("CONF_COMPLETED_ORDER_STATUS"));
            if (!empty($completedOrderStatus)) {
                $completedOrderStatus = implode(",", unserialize(FatApp::getConfig("CONF_COMPLETED_ORDER_STATUS")));
            } else {
                $completedOrderStatus = 0;
            }

            $rsSales = FatApp::getDb()->query("SELECT SUM((op_unit_price*op_qty) + COALESCE(opcharge_amount,0) - op_refund_amount) AS Sales FROM `tbl_order_products` t1 LEFT OUTER JOIN tbl_order_product_charges opc on opc.opcharge_op_id = t1.op_id and opc.opcharge_type = " . OrderProduct::CHARGE_TYPE_SHIPPING . "  LEFT OUTER JOIN tbl_order_products_data opd on opd.opd_op_id = t1.op_id INNER JOIN tbl_orders t2 on t1.op_order_id=t2.order_id INNER JOIN tbl_shops ts on ts.shop_id=t1.op_shop_id  WHERE t2.order_payment_status = 1 and t1.op_status_id in (" . $completedOrderStatus . ") and month( t2.`order_date_added` )= $val[monthCount] and year( t2.`order_date_added` )= $val[year] and ts.shop_user_id=" . (int) $userId .' AND opd.opd_sold_or_rented ='. applicationConstants::PRODUCT_FOR_RENT);

            $row = FatApp::getDb()->fetch($rsSales);

            $sales_arr[$val['monthShort'] . "-" . $val['yearShort']] = $row["Sales"];
        }
        return $sales_arr;
    }

}
