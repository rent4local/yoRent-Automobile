<?php

defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    'rfq_id' => Labels::getLabel('LBL_Request_ID', $siteLangId),
    'order_id' => Labels::getLabel('LBL_Order_Id', $siteLangId),
    'selprod_title' => Labels::getLabel('LBL_Product_name', $siteLangId),
    'rfq_quantity' => Labels::getLabel('LBL_Qty', $siteLangId),
    'rfq_request_type' => Labels::getLabel('LBL_Request_For', $siteLangId),
    'rfq_added_on' => Labels::getLabel('LBL_Date', $siteLangId),
    'rfq_status' => Labels::getLabel('LBL_Status', $siteLangId),
    'action' => '',
);

if ($type != RequestForQuote::APPROVED_LIST) {
    unset($arr_flds['order_id']);
}

/* $tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table')); */
$tableClass = '';
    if (0 < count($arr_listing)) {
        $tableClass = "table-justified";
    }
    $tbl = new HtmlElement('table', array('class' => 'table ' . $tableClass));
$th = $tbl->appendElement('thead')->appendElement('tr', array('class' => ''));
foreach ($arr_flds as $val) {
    $e = $th->appendElement('th', array(), $val);
}

foreach ($arr_listing as $sn => $row) {
    $link = CommonHelper::generateUrl('RequestForQuotes', 'requestView', array($row['rfq_id']));
    $tr = $tbl->appendElement('tr', array('class' => ''));

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'rfq_id':
                $td->appendElement('plaintext', array(), '#' . $row[$key], true);
                break;
            case 'rfq_status':
                $td->appendElement('plaintext', array(), $statusArr[$row[$key]], true);
                break;
            case 'rfq_added_on':
                $td->appendElement('plaintext', array(), FatDate::Format($row[$key]), true);
                break;
            case 'rfq_request_type':
                $rfqFor = ($row[$key] == applicationConstants::PRODUCT_FOR_SALE) ? Labels::getLabel('LBL_Sale', $siteLangId) : Labels::getLabel('LBL_Rent', $siteLangId);
            
                $td->appendElement('plaintext', array(), $rfqFor, true); 
                break;
            case 'action':
                $ul = $td->appendElement('ul', ['class' => 'actions']);
                $li = $ul->appendElement('li');
                $li->appendElement('a', array('href' => $link, 'class' => 'btn btn--primary btn--sm', 'title' => Labels::getLabel('LBL_View', $siteLangId)), '<i class="far fa fa-eye icon"></i>', true);
                
                if ($row['order_id'] != '' && $row['order_payment_status'] == Orders::ORDER_PAYMENT_PENDING && strtotime($row['rfq_quote_validity']) >= strtotime(date('Y-m-d')) && $row['rfq_status'] == RequestForQuote::REQUEST_APPROVED && $row['invoice_status'] == Invoice::INVOICE_IS_SHARED_WITH_BUYER) {    
                    $li = $ul->appendElement('li');    
                    $li->appendElement(
                        'a', array('href' => CommonHelper::generateUrl('RfqCheckout', 'index', array($row['order_id'])), 'class' => 'btn btn--primary btn--sm', 'title' => Labels::getLabel('LBL_Pay_Now', $siteLangId)), '<i class="far fa fa-file-invoice icon"></i>', true
                    );
                }
                
                
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
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmSearchQuotesRequestsPaging'));

$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'callBackJsFunc' => 'goToSearchPage', 'siteLangId' => $siteLangId, 'pageSize' => $pageSize, 'removePageCentClass' => true, 'recordCount' => $recordCount);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
