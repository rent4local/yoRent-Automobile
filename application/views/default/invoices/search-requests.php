<?php

defined('SYSTEM_INIT') or die('Invalid Usage.');

/* echo '<pre>';
  print_r($listingData);
  echo '</pre>';
  exit; */

$arr_flds = array(
    'inreq_order_id' => Labels::getLabel('LBL_Order_Id', $siteLangId),
    'buyer_name' => Labels::getLabel('LBL_Buyer', $siteLangId),
    'inreq_reason' => Labels::getLabel('LBL_Reason', $siteLangId),
    'inreq_added_on_date' => Labels::getLabel('LBL_Request_Date', $siteLangId),
    'inreq_status' => Labels::getLabel('LBL_Status', $siteLangId),
    'action' => '',
);

$tbl = new HtmlElement('table', array('class' => 'table'));
$th = $tbl->appendElement('thead')->appendElement('tr', array('class' => ''));
foreach ($arr_flds as $val) {
    $e = $th->appendElement('th', array(), $val);
}

$sr_no = 0;
foreach ($listingData as $sn => $request) {
    $sr_no++;
    $tr = $tbl->appendElement('tr', array('class' => ''));
    $orderDetailUrl = UrlHelper::generateUrl('Invoices', 'create', array($request['inreq_order_id']));

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'inreq_order_id':
                $txt = '<a title="' . Labels::getLabel('LBL_View_Order_Detail', $siteLangId) . '" href="' . $orderDetailUrl . '">';
                $txt .= $request['inreq_order_id'];
                //$txt .= '</a><br/>' . FatDate::format($order['order_date_added']);
                $td->appendElement('plaintext', array(), $txt, true);
                break;
            case 'buyer_name':
                $td->appendElement('plaintext', array(), $request['buyer_name'], true);
                break;

            case 'inreq_status':
                $class = 'default';
                if ($request['inreq_status'] == InvoiceRequest::INVOICE_REQUEST_COMPLETE) {
                    $class = 'success';
                }

                $txt = '<label class="badge badge-' . $class . '">' . $statusArr[$request['inreq_status']] . '</label>';
                $td->appendElement('plaintext', array(), $txt, true);
                break;
            case 'inreq_added_on_date':
                $txt = FatDate::format($request['inreq_added_on_date']);
                $td->appendElement('plaintext', array(), $txt, true);
                break;
            case 'action':
                if ($request['inreq_status'] == InvoiceRequest::INVOICE_REQUEST_PENDING && $canEdit) {
                    $ul = $td->appendElement("ul", array("class" => "actions"), '', true);
                    $li = $ul->appendElement("li");
                    $li->appendElement(
                            'a', array('href' => $orderDetailUrl, 'class' => '', 'title' => Labels::getLabel('LBL_Generate_Invoice', $siteLangId)), '<i class="fa fa-file-invoice"></i>', true
                    );
                }
                break;
            default:
                $td->appendElement('plaintext', array(), '' . $request[$key], true);
                break;
        }
    }
}

echo $tbl->getHtml();
if (count($listingData) == 0) {
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId, 'message' => Labels::getLabel('LBL_No_Records_Found', $siteLangId)));
}

$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmRequestSrchPaging'));
$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'callBackJsFunc' => 'goToRequestSearchPage', 'siteLangId' => $siteLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
