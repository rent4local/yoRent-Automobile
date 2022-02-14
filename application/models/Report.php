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

        if (empty($attr)) {
            $srch->addMultipleFields(
                    array('DATE(order_date_added) as order_date', 'count(op_id) as totOrders', 'SUM(op_qty) as totQtys', 'SUM(op_refund_qty) as totRefundedQtys', 'SUM(op_qty - op_refund_qty) as netSoldQty', 'sum((op_commission_charged - op_refund_commission)) as totalSalesEarnings',
                        'sum(op_refund_amount) as totalRefundedAmount', 'op.op_qty', 'op.op_unit_price', 'op.op_unit_cost',
                        'SUM( op.op_unit_cost * op_qty ) as inventoryValue', 'op_other_charges', 'sum(( op_unit_price * op_qty ) + COALESCE(op_other_charges, 0)  + op_rounding_off) as orderNetAmount',
                        '(SUM(optax.opcharge_amount)) as taxTotal', '(SUM(opship.opcharge_amount)) as shippingTotal', 'op_rounding_off', '(SUM((opd_rental_price * op_qty))) as total_rental_total', 'sum(opd_rental_security * op_qty) as totalRentalSecurity'));
        } else {
            $srch->addMultipleFields($attr);
        }
        return $srch;
    }

}
