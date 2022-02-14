<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="scroll scroll-x js-scrollable table-wrap">
    <?php
    $arr_flds = array(
        'ocrequest_id' => Labels::getLabel('LBL_ID', $siteLangId),
        'ocrequest_date' => Labels::getLabel('LBL_Date', $siteLangId),
        'op_invoice_number' => Labels::getLabel('LBL_Order_Id/Invoice_Number', $siteLangId),
        'ocreason_title' => Labels::getLabel('LBL_Request_Details', $siteLangId),
        'ocrequest_status' => Labels::getLabel('LBL_Status', $siteLangId),
        'amount' => Labels::getLabel('LBL_Amount', $siteLangId),
        'op_qty' => Labels::getLabel('LBL_Purchased_Quantity', $siteLangId),
        'opd_rental_security' => Labels::getLabel('LBL_SECURITY_AMOUNT_(PER_QTY)', $siteLangId),
    );

    if ($orderType != applicationConstants::ORDER_TYPE_RENT) {
        unset($arr_flds['opd_rental_security']);
    }

    $tbl = new HtmlElement('table', array('class' => 'table'));
    $th = $tbl->appendElement('thead')->appendElement('tr', array('class' => ''));
    foreach ($arr_flds as $val) {
        $e = $th->appendElement('th', array(), $val);
    }

    $sr_no = 0;
    foreach ($requests as $sn => $row) {
        $sr_no++;
        $tr = $tbl->appendElement('tr', array('class' => ''));

        foreach ($arr_flds as $key => $val) {
            $td = $tr->appendElement('td');
            switch ($key) {
                case 'ocrequest_id':
                    $td->appendElement('plaintext', array(), str_pad($row[$key], 5, '0', STR_PAD_LEFT), true);
                    break;
                case 'ocrequest_date':
                    $td->appendElement('plaintext', array(), FatDate::format($row[$key]), true);
                    break;
                case 'ocreason_title':
                    $txt = '<strong>' . Labels::getLabel('LBL_Reason', $siteLangId) . ': </strong>';
                    $txt .= CommonHelper::displayNotApplicable($siteLangId, $row['ocreason_title']);
                    $txt .= '<br/><strong>' . Labels::getLabel('LBL_Comments', $siteLangId) . ': </strong>';
                    $txt .= nl2br(CommonHelper::displayNotApplicable($siteLangId, $row['ocrequest_message']));
                    $td->appendElement('plaintext', array(), $txt, true);
                    break;
                case 'ocrequest_status':
                    $td->appendElement('span', array('class' => 'label label-inline ' . $cancelReqStatusClassArr[$row[$key]]), $OrderCancelRequestStatusArr[$row[$key]], true);
                    break;
                case 'amount':
					$orderTotalAmount = CommonHelper::orderProductAmount($row, 'netamount');
                    $amt = CommonHelper::displayMoneyFormat($orderTotalAmount, true, true);
                    if ($row['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT && $row['ocrequest_is_penalty_applicable'] > 0) {
                        $shippingCharges = $row['shipping_charges'];
						$orderTotalAmount = CommonHelper::orderProductAmount($row, 'netamount') - ($row['opd_rental_security'] * $row['op_qty']) - $shippingCharges;
                        $refunableAmount = ($orderTotalAmount * $row['ocrequest_refund_amount'] / 100) + $shippingCharges;
                        $amt .= '<br>' . sprintf(Labels::getLabel('LBL_Order_rental_start_date_%s', $siteLangId), FatDate::format($row['opd_rental_start_date'], true));
                        $amt .= '<br>' . sprintf(Labels::getLabel('LBL_Order_Cancel_Before_%s_hours', $siteLangId), $row['ocrequest_hours_before_rental']);
                        $amt .= '<br>' . sprintf(Labels::getLabel('LBL_Refundable_Amount(After_Penalty_%s_)', $siteLangId), $row['ocrequest_refund_amount'] . '%') . ' ' . CommonHelper::displayMoneyFormat($refunableAmount, true, true) . '<small>(' . Labels::getLabel('LBL_Exc_Security_Amount', $siteLangId) . ')</small>';
                    }
                    $td->appendElement('plaintext', array(), $amt, true);
                    break;
                case 'opd_rental_security':
                    $amt = CommonHelper::displayMoneyFormat($row['opd_rental_security'], true, true);
                    $td->appendElement('plaintext', array(), $amt, true);
                    break;
                default:
                    $td->appendElement('plaintext', array(), $row[$key], true);
                    break;
            }
        }
    }
    echo $tbl->getHtml();
    if (count($requests) == 0) {
        $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
        $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId, 'message' => $message));
    }
    ?>
</div>
<?php
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmOrderCancellationRequestSrchPaging'));
$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'callBackJsFunc' => 'goToOrderCancelRequestSearchPage', 'siteLangId' => $siteLangId, 'pageSize' => $pageSize, 'removePageCentClass' => true);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
