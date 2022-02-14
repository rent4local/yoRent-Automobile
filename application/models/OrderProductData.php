<?php

class OrderProductData extends MyAppModel
{

    public const DB_TBL = 'tbl_order_products_data';
    public const DB_TBL_PREFIX = 'opd_';

    public function __construct(int $id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'op_id', $id);
    }

    public static function getSearchObject(int $langId = 0)
    {
        $srch = new SearchBase(static::DB_TBL, 'opd');
        return $srch;
    }

    public static function getOrderProductData(int $opId, bool $extendedOrder = false)
    {
        $srch = self::getSearchObject();
        if ($extendedOrder) {
            $srch->addCondition('opd_extend_from_op_id', '=', $opId);
        } else {
            $srch->addCondition('opd_op_id', '=', $opId);
        }
        $rs = $srch->getResultSet();
        $data = FatApp::getDb()->fetch($rs);
        return $data;
    }

    /* Get order of particular product for particular time interval */

    public static function getProductOrders(int $prodId, string $startDate, string $endDate,  $prodBufferDays = 0, int $extendRental = 0)
    {
        if (1 > $prodId) {
            return false;
        }
        
        $processingStatuses = FatApp::getConfig('CONF_PROCESSING_ORDER_STATUS');
        $paymentConfirmStatus = FatApp::getConfig('CONF_DEFAULT_PAID_ORDER_STATUS', FatUtility::VAR_INT, 0);
        $readyForRentalReturn = FatApp::getConfig('CONF_DEFAULT_READY_FOR_RENTAL_RETURN_BUYER_END', FatUtility::VAR_INT, 0);
        $processingStatuses = unserialize($processingStatuses);
        $processingStatuses = array_merge($processingStatuses, [$paymentConfirmStatus, $readyForRentalReturn, OrderStatus::ORDER_COMPLETED, OrderStatus::ORDER_RENTAL_RETURNED]);
        
        $unavailableQty = 0;
        $prodBufferDays = (int) $prodBufferDays;

        $prodstartBufferDays = ($extendRental == 1) ? 0 : (int) $prodBufferDays;
        $srch = new SearchBase('tbl_order_products', 'op');
        $srch->joinTable(static::DB_TBL, 'LEFT OUTER JOIN', 'op.op_id = opd.opd_op_id', 'opd');
        $srch->joinTable(Orders::DB_TBL_ORDER_PRODUCTS_SHIPPING, 'LEFT OUTER JOIN', 'op.op_id = ship.opshipping_op_id', 'ship');
        $srch->addCondition('op_selprod_id', '=', intval($prodId));
        $srch->addCondition('opd_sold_or_rented', '=', 2);
        $srch->addCondition('op_status_id', 'IN', $processingStatuses);
        
        $srch->joinTable(Orders::DB_TBL_ORDER_STATUS_HISTORY, 'LEFT OUTER JOIN', 'oshistory_op_id = op.op_id AND (oshistory_orderstatus_id = '. OrderStatus::ORDER_COMPLETED . ' OR oshistory_orderstatus_id = '. OrderStatus::ORDER_RENTAL_RETURNED .') AND oshistory_date_added < opd_rental_end_date', 'opstatus');
        
        $srch->addFld('op_id, (op_qty - op_return_qty) as op_qty, opd_rental_start_date, IFNULL((opstatus.oshistory_date_added), opd_rental_end_date) as opd_rental_end_date, IFNULL(opshipping_ship_duration, 0) as ship_days, IFNULL(oshistory_orderstatus_id, 0) as op_status');

        /* Please check this condition */
        $srch->addDirectCondition('(("' . $startDate . '" >= opd_rental_start_date AND "' . $startDate . '" <= ((opd_rental_end_date + INTERVAL opshipping_ship_duration DAY) + INTERVAL ' . $prodstartBufferDays . ' DAY)) OR ("' . $endDate . '" >= ((opd_rental_start_date - INTERVAL opshipping_ship_duration DAY) - INTERVAL ' . $prodBufferDays . ' DAY) AND "' . $endDate . '" <= opd_rental_end_date) OR ("' . $startDate . '" <= opd_rental_start_date AND "' . $endDate . '" >=  opd_rental_end_date))');
        $srch->addGroupBy('op_id');
        /* $srch->addOrder('oshistory_orderstatus_id'); */
        
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $query = $srch->getQuery();
        
        $query .= ' UNION ';

        $srch = new SearchBase('tbl_prod_unavailable_rental_durations');
        $srch->addFld('"0" as op_id, pu_quantity as op_qty,  pu_start_date as opd_rental_start_date, pu_end_date as opd_rental_end_date, 0 as ship_days, 0 as op_status');
        $srch->addCondition('pu_selprod_id', '=', intval($prodId));

        $srch->addDirectCondition('(("' . $startDate . '" >= pu_start_date AND "' . $startDate . '" <= (pu_end_date + INTERVAL ' . $prodBufferDays . ' DAY)) OR ("' . $endDate . '" >= (pu_start_date - INTERVAL ' . $prodBufferDays . ' DAY) AND "' . $endDate . '" <= pu_end_date) OR ("' . $startDate . '" <= pu_start_date AND "' . $endDate . '" >=  pu_end_date))');

        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $query .= $srch->getQuery();
        $rs = FatApp::getDb()->query($query);
        return FatApp::getDb()->fetchAll($rs);
    }

}
