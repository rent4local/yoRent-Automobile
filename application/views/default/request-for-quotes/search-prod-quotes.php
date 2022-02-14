<?php

defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    'rfq_id' => Labels::getLabel('LBL_Request_ID', $siteLangId),
    'order_id' => Labels::getLabel('LBL_Order_Id', $siteLangId),
    'selprod_title' => Labels::getLabel('LBL_Product_name', $siteLangId),
    'rfq_quantity' => Labels::getLabel('LBL_Qty', $siteLangId),
    'rfq_request_type' => Labels::getLabel('LBL_Request_For', $siteLangId),
    // 'rfq_capacity' => Labels::getLabel('LBL_Capacity', $siteLangId),
    'rfq_added_on' => Labels::getLabel('LBL_Date', $siteLangId),
    'rfq_status' => Labels::getLabel('LBL_Status', $siteLangId),
    'action' => '',
);

if ($type != RequestForQuote::APPROVED_LIST) {
    unset($arr_flds['order_id']);
}

$tableClass = '';
    if (0 < count($arr_listing)) {
        $tableClass = "table-justified";
    }
    $tbl = new HtmlElement('table', array('class' => 'table ' . $tableClass));

/* $tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table')); */
$th = $tbl->appendElement('thead')->appendElement('tr', array('class' => ''));
foreach ($arr_flds as $val) {
    $e = $th->appendElement('th', array(), $val);
}

foreach ($arr_listing as $sn => $row) {
    $link = CommonHelper::generateUrl('RequestForQuotes', 'view', array($row['rfq_id']));
    $tr = $tbl->appendElement('tr', array('class' => ''));

    foreach ($arr_flds as $key => $val) {
        $rfqId = $row['rfq_id'];
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'rfq_id':
                $td->appendElement('plaintext', array(), '#' . $row[$key], true);
                break;
            case 'rfq_status':
                $td->appendElement('plaintext', array(), $statusArr[$row[$key]], true);
                break;
                
            case 'rfq_request_type':
                $rfqFor = ($row[$key] == applicationConstants::PRODUCT_FOR_SALE) ? Labels::getLabel('LBL_Sale', $siteLangId) : Labels::getLabel('LBL_Rent', $siteLangId);
            
                $td->appendElement('plaintext', array(), $rfqFor, true);
                break;
            case 'rfq_added_on':
                $td->appendElement('plaintext', array(), FatDate::Format($row[$key]), true);
                break;
            case 'action':
                $html = '<ul class="actions">';
                $html .= '<li><a href="' . $link . '"><span data-toggle="tooltip" data-placement="top" title="" data-original-title="View detail"><i class="fa fa-eye"></i></span></a></li>';
                if ($row['rfq_status'] == RequestForQuote::REQUEST_ACCEPTED_BY_BUYER) {
                    $html .= '<li><a href="javascript:void(0)" onclick="changeStatus(' . $rfqId . ', ' . RequestForQuote::REQUEST_APPROVED . ')"><span data-toggle="tooltip" data-placement="top" title="" data-original-title="Approve & Confirm"><i class="fa fa-check-circle"></i></span></a></li>';
                }
                if ($row['order_id'] != '' && $row['invoice_status'] != Invoice::INVOICE_IS_SHARED_WITH_BUYER && $row['order_payment_status'] != Orders::ORDER_PAYMENT_PAID) {
                    $html .= '<li><a href="' . CommonHelper::generateUrl('invoices', 'create', [$row['order_id']]) . '"><span data-toggle="tooltip" data-placement="top" title="" data-original-title="' . Labels::getLabel('LBL_Create_Invoice', $siteLangId) . '"><i class="fa fa-file-invoice"></i></span></a></li>';
                }
                /* if ($row['order_id'] != '' && $row['invoice_status'] == Invoice::INVOICE_IS_SHARED_WITH_BUYER && $row['order_payment_status'] != Orders::ORDER_PAYMENT_PAID) {
                    $html .= '<li><a href="' . CommonHelper::generateUrl('invoices', 'view', [$row['order_id']]) . '"><span data-toggle="tooltip" data-placement="top" title="" data-original-title="' . Labels::getLabel('LBL_View_Invoice', $siteLangId) . '"><i class="fa fa-file-invoice"></i></span></a></li>';
                } */

                $html .= '</ul>';
                $td->appendElement('plaintext', array(), $html, true);
                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
}

echo $tbl->getHtml();
if (count($arr_listing) == 0) {
    $message = Labels::getLabel('LBL_No_Record_found', $siteLangId);
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId, 'message' => $message));
}
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmSearchProductQuotesPaging'));

$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'callBackJsFunc' => 'goToSearchPage', 'siteLangId' => $siteLangId, 'pageSize' => $pageSize, 'removePageCentClass' => true, 'recordCount' => $recordCount);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false); ?>

<script>
changeStatus = function (rfqId, status) {
    var data = 'rfq_id=' + rfqId + '&status=' + status;
    fcom.updateWithAjax(fcom.makeUrl('CounterOffer', 'updateStatusBySeller'), data, function (ans) {
        if (ans.status == 1) {
            $.mbsmessage(ans.msg, true, 'alert--success');
            window.location.reload();
        } else {
            $.mbsmessage(ans.msg, true, 'alert--danger');
        }
    });
}
</script>
    
    
    

