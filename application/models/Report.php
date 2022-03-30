<?php

class Report extends MyAppModel
{

    public static function salesReportObject($langId = 0, $joinSeller = false, $attr = array(), $type = applicationConstants::PRODUCT_FOR_SALE)
    {
        $ocSrch = new SearchBase(OrderProduct::DB_TBL_CHARGES, 'opc');
        $ocSrch->doNotCalculateRecords();
        $ocSrch->doNotLimitRecords();
        $ocSrch->addMultipleFields(array('opcharge_op_id', 'sum(opcharge_amount) as op_other_charges'));
        $ocSrch->addGroupBy('opc.opcharge_op_id');
        $qryOtherCharges = $ocSrch->getQuery();

        $srch = new OrderProductSearch($langId, true);
        $srch->joinPaymentMethod();

        if ($joinSeller) {
            $srch->joinSellerUser();
        }

        $srch->joinTable('(' . $qryOtherCharges . ')', 'LEFT OUTER JOIN', 'op.op_id = opcc.opcharge_op_id', 'opcc');
        $srch->joinOrderProductCharges(OrderProduct::CHARGE_TYPE_TAX, 'optax');
        $srch->joinOrderProductCharges(OrderProduct::CHARGE_TYPE_SHIPPING, 'opship');

        $cnd = $srch->addCondition('o.order_payment_status', '=', Orders::ORDER_PAYMENT_PAID);
        $cnd->attachCondition('plugin_code', '=', 'cashondelivery');
        $cnd->attachCondition('plugin_code', '=', 'payatstore');
        $srch->addStatusCondition(unserialize(FatApp::getConfig('CONF_COMPLETED_ORDER_STATUS')));
        $srch->addCondition('opd_sold_or_rented', '=', $type);

		$cancellOrderStatus = FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS", FatUtility::VAR_INT, 0);

        if (empty($attr)) {
            $srch->addMultipleFields(
                    array('DATE(order_date_added) as order_date', 'count(op_id) as totOrders', 'SUM(op_qty) as totQtys', 'SUM(op_refund_qty) as totRefundedQtys', 'SUM(op_qty - op_refund_qty) as netSoldQty', 'sum(IF(op_status_id != '. $cancellOrderStatus .', (op_commission_charged - op_refund_commission), 0)) as totalSalesEarnings', 'sum(op_refund_amount) as totalRefundedAmount', 'op.op_qty', 'op.op_unit_price', 'op.op_unit_cost', 'SUM( op.op_unit_cost * op_qty ) as inventoryValue', 'op_other_charges', 'sum(( op_unit_price * op_qty ) + COALESCE(op_other_charges, 0)  + op_rounding_off) as orderNetAmount', '(SUM(IF(op_status_id != '. $cancellOrderStatus .', optax.opcharge_amount, 0))) as taxTotal', '(SUM(IF(op_status_id != '. $cancellOrderStatus .', opship.opcharge_amount, 0))) as shippingTotal', 'op_rounding_off', '(SUM((opd_rental_price * op_qty))) as total_rental_total', 'sum(IF(op_status_id != '. $cancellOrderStatus .', opd_rental_security * op_qty , 0)) as totalRentalSecurity', 'SUM(IF(op_status_id = '. $cancellOrderStatus .', 1, 0)) as cancelledOrders', 'SUM(IF(op_status_id = '. $cancellOrderStatus .', op_qty, 0)) as cancelledOrdersQty', 'SUM(IF(op_status_id = '. $cancellOrderStatus .', (( op_unit_price * op_qty ) + COALESCE(op_other_charges, 0)  + op_rounding_off), 0)) as cancelledOrdersAmt'));
        } else {
            $srch->addMultipleFields($attr);
        }
        return $srch;
    }

}
