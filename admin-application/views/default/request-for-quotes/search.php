<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    'rfq_id'=> Labels::getLabel('LBL_Request_ID', $adminLangId),
    'buyer_name'=> Labels::getLabel('LBL_Buyer_Name', $adminLangId),
    'seller_name'=> Labels::getLabel('LBL_Seller_Name', $adminLangId),
    'selprod_title' => Labels::getLabel('LBL_Product_name', $adminLangId),
    'rfq_quantity' => Labels::getLabel('LBL_Qty', $adminLangId),
    //'rfq_capacity' => Labels::getLabel('LBL_Capacity', $adminLangId),
    'rfq_added_on' => Labels::getLabel('LBL_Date', $adminLangId),
    'rfq_status' => Labels::getLabel('LBL_Status', $adminLangId),
    'action' => Labels::getLabel('LBL_Action', $adminLangId),
);

$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table'));
$th = $tbl->appendElement('thead')->appendElement('tr', array('class' => ''));
foreach ($arr_flds as $key => $val) {
    if ($key == 'selprod_title') {
        $e = $th->appendElement('th', array('width'=>'20%'), $val);
    } else if ($key == 'seller_name') {
        $e = $th->appendElement('th', array('width'=>'15%'), $val);
    } else {
        $e = $th->appendElement('th', array(), $val);
    }
    
}

// CommonHelper::printArray($arr_listing, true);
foreach ($arr_listing as $sn => $row) {
    $link = CommonHelper::generateUrl('RequestForQuotes', 'requestView', array($row['rfq_id']));
    $tr = $tbl->appendElement('tr', array('class' => ''));

    foreach ($arr_flds as $key => $val) {
        $rfqId = $row['rfq_id'];
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'rfq_id':
                $td->appendElement('plaintext', array(), '#'.$row[$key], true);
                break;
            case 'rfq_status':
                $td->appendElement('plaintext', array(), $statusArr[$row[$key]], true);
                break;
            case 'rfq_added_on':
                $td->appendElement('plaintext', array(), FatDate::Format($row[$key]), true);
                break;
            case 'action':
                $html = '';
                    $html = '<a href="'.CommonHelper::generateUrl('RequestForQuotes', 'view', array($row['rfq_id'])).'" class="btn btn-clean btn-sm btn-icon" title="View"><i class="far fa fa-eye icon"></i></a>';
                    if (true === RequestForQuote::canAdminUpdateStatus($row['rfq_status'])) {
                        $html .= '<a href="javascript:void(0)" onclick="changeRfqStatus('.$rfqId.')" class="btn btn-clean btn-sm btn-icon" title="Close RFQ"><i class="far fa fa-times icon"></i></a>';
                    }
                $td->appendElement('plaintext', array(), $html, true);
                break;
            case 'buyer_name':
                $buyerName = "<a href='javascript:void(0)' onclick='redirectfunc(\"" . UrlHelper::generateUrl('Users') . "\", " . $row['buyer_user_id'] . ")'>" . $row['buyer_name'] . "</a>";

                $td->appendElement('plaintext', array(), $buyerName, true);
                break;
            case 'seller_name':
                $shopName = "<a href='javascript:void(0)' onclick='redirectfunc(\"" . UrlHelper::generateUrl('Shops') . "\", " . $row['shop_id'] . ")'>" . $row['shop_name'] . "</a>";
                $sellerName = "<a href='javascript:void(0)' onclick='redirectfunc(\"" . UrlHelper::generateUrl('Users') . "\", " . $row['seller_user_id'] . ")'>" . $row['seller_name'] . "</a>";

                $td->appendElement('plaintext', array(), 'Shop: '.$shopName.'<br>'.$sellerName, true);
                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
}


if (count($arr_listing) == 0) {
    $tbl->appendElement('tr')->appendElement('td', array('colspan' => count($arr_flds)), Labels::getLabel('LBL_No_Records_Found', $adminLangId));
}
echo $tbl->getHtml();
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array(
    'name' => 'frmRfqSearchPaging'
));
$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'pageSize' => $pageSize, 'recordCount' => $recordCount, 'adminLangId' => $adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);


