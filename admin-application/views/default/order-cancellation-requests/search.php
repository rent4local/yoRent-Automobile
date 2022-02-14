<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php

$arr_flds = array(
    'ocrequest_id' => Labels::getLabel('LBL_ID', $adminLangId),
    'buyer_detail' => Labels::getLabel('LBL_Buyer_Details', $adminLangId),
    'vendor_detail' => Labels::getLabel('LBL_Seller_Details', $adminLangId),
    'reuqest_detail' => Labels::getLabel('LBL_Order_Details', $adminLangId),
    'amount' => Labels::getLabel('LBL_Amount', $adminLangId),
	'op_qty' => Labels::getLabel('LBL_Qty', $adminLangId),
    'opd_rental_security' => Labels::getLabel('LBL_Security_(Per_Qty)', $adminLangId),
    'ocrequest_date' => Labels::getLabel('LBL_Date', $adminLangId),
    'ocrequest_status' => Labels::getLabel('LBL_Status', $adminLangId),
    'action' => '',
);

if ($orderType != applicationConstants::ORDER_TYPE_RENT) {
    unset($arr_flds['opd_rental_security']);
}

$tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table table--hovered table-responsive'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $val) {
    $e = $th->appendElement('th', array(), $val);
}
$sr_no = $page == 1 ? 0 : $pageSize * ($page - 1);
foreach ($arrListing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr');

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'ocrequest_id':
                $ocrequest_id = '#C' . str_pad($row["ocrequest_id"], 5, '0', STR_PAD_LEFT);
                $td->appendElement('plaintext', array(), $ocrequest_id);
                break;
            case 'buyer_detail':
                $txt = '<strong>' . Labels::getLabel('LBL_N', $adminLangId) . ': </strong>' . $row['buyer_name'];
                /* $txt .= '<br/><strong>' . Labels::getLabel('LBL_U', $adminLangId) . ':  </strong>' . $row['buyer_username'];
                $txt .= '<br/><strong>' . Labels::getLabel('LBL_E', $adminLangId) . ': </strong>' . $row['buyer_email'];
                $txt .= '<br/><strong>' . Labels::getLabel('LBL_P', $adminLangId) . ': </strong>' . $row['buyer_phone']; */
                $td->appendElement('plaintext', array(), $txt, true);
                break;
            case 'vendor_detail':
                $txt = '<strong>' . Labels::getLabel('LBL_N', $adminLangId) . ': </strong>' . $row['seller_name'];
                /* $txt .= '<br/><strong>' . Labels::getLabel('LBL_U', $adminLangId) . ': </strong>' . $row['seller_username'];
                $txt .= '<br/><strong>' . Labels::getLabel('LBL_E', $adminLangId) . ': </strong>' . $row['seller_email'];
                $txt .= '<br/><strong>' . Labels::getLabel('LBL_P', $adminLangId) . ': </strong>' . $row['seller_phone']; */
                $td->appendElement('plaintext', array(), $txt, true);
                break;
            case 'reuqest_detail':
                $txt = '<strong>' . Labels::getLabel('LBL_Order/Invoice', $adminLangId) . ': </strong>' . $row['op_invoice_number'];
                $txt .= '<br/><strong>' . Labels::getLabel('LBL_Order_Status', $adminLangId) . ': </strong>' . $row['orderstatus_name'];
                /* $txt .= '<br/><strong>' . Labels::getLabel('LBL_Reason', $adminLangId) . ': </strong>' . $row['ocreason_title'];
                $txt .= '<br/><strong>' . Labels::getLabel('LBL_Comments', $adminLangId) . ': </strong>' . nl2br($row['ocrequest_message']); */
                $td->appendElement('plaintext', array(), $txt, true);
                break;
            case 'amount':
                $orderTotalAmount = CommonHelper::orderProductAmount($row, 'netamount');
                $amt = CommonHelper::displayMoneyFormat($orderTotalAmount, true, true);
                /* if ($row['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT && $row['ocrequest_is_penalty_applicable'] > 0) {
                    $shippingCharges = $row['shipping_charges'];
                
                    $orderTotalAmount = CommonHelper::orderProductAmount($row, 'netamount') - ($row['opd_rental_security'] * $row['op_qty']) - $shippingCharges;
                    $refunableAmount = ($orderTotalAmount * $row['ocrequest_refund_amount'] / 100) + $shippingCharges;
                    $amt .= '<br>' . sprintf(Labels::getLabel('LBL_Order_rental_start_date_%s', $adminLangId), date('M d, Y ', strtotime($row['opd_rental_start_date'])));
                    $amt .= '<br>' . sprintf(Labels::getLabel('LBL_Order_Cancel_Before_%s_hours', $adminLangId), $row['ocrequest_hours_before_rental']);
                    $amt .= '<br>' . sprintf(Labels::getLabel('LBL_Refundable_Amount(After_Penalty_%s_)', $adminLangId), $row['ocrequest_refund_amount'] . '%') . ' ' . CommonHelper::displayMoneyFormat($refunableAmount, true, true) . '<small>(' . Labels::getLabel('LBL_Exc_Security_Amount', $adminLangId) . ')</small>';
                } */
                $td->appendElement('plaintext', array(), $amt, true);
                break;
            case 'opd_rental_security':
                $amt = CommonHelper::displayMoneyFormat($row['opd_rental_security'], true, true);
                $td->appendElement('plaintext', array(), $amt, true);
                break;

            case 'ocrequest_status':
                $td->appendElement('label', array('class' => 'label label--' . $statusClassArr[$row[$key]] . ''), $requestStatusArr[$row[$key]]);
                break;
            case 'action':
                if ($canEdit && $row['ocrequest_status'] == OrderCancelRequest::CANCELLATION_REQUEST_STATUS_PENDING) {
                    $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Edit', $adminLangId), "onclick" => "updateStatusForm(" . $row['ocrequest_id'] . ")"), "<i class='far fa-edit icon'></i>", true);
                }
                $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_View', $adminLangId), "onclick" => "viewRequest(" . $row['ocrequest_id'] . ")"), "<i class='far fa-eye icon'></i>", true);
                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
}
if (count($arrListing) == 0) {
    $tbl->appendElement('tr')->appendElement('td', array('colspan' => count($arr_flds)), Labels::getLabel('LBL_No_Records_Found', $adminLangId));
}
echo $tbl->getHtml();
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array(
    'name' => 'frmOrderCancellationRequestSearchPaging'
));
$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'pageSize' => $pageSize, 'recordCount' => $recordCount, 'adminLangId' => $adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
?>