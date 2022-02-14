<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>

<?php

$arr_flds = array(
    'counter_offer_added_on' => Labels::getLabel('LBL_Date', $adminLangId),
    'counter_offer_total_cost' => Labels::getLabel('LBL_Total_Cost', $adminLangId),
    'counter_offer_shipping_cost' => Labels::getLabel('LBL_Shipping_Cost', $adminLangId),
    'counter_offer_status' => Labels::getLabel('LBL_Status', $adminLangId),
    'counter_offer_comment' => Labels::getLabel('LBL_comment', $adminLangId),
);

if (!empty($rfqData['rfq_fulfilment_type'] != Shipping::FULFILMENT_SHIP)) { 
    unset($arr_flds['counter_offer_shipping_cost']);
}


$tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table'));
$th = $tbl->appendElement('thead')->appendElement('tr', array('class' => ''));
foreach ($arr_flds as $key => $val) {
    if ($key == 'counter_offer_comment') {
        $e = $th->appendElement('th', array('width' => '30%'), $val);
    } else {
        $e = $th->appendElement('th', array(), $val);
    }
}

foreach ($arr_listing as $sn => $row) {

    if ($sn == 0) {
        $tr = $tbl->appendElement('tr', array('class' => '', 'id' => 'count-offer-form-js'));
    } else {
        $tr = $tbl->appendElement('tr', array('class' => ''));
    }

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        $rfqId = $row['counter_offer_rfq_id'];
        $counterOfferId = $row['counter_offer_id'];
        $status = $rfqData['rfq_status'];
        switch ($key) {
            case 'counter_offer_total_cost' :
            case 'counter_offer_shipping_cost' :
                $td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($row[$key]), true);
                break;

            case 'counter_offer_status':
                /* $td->appendElement('plaintext', array(), $statusArr[$row[$key]], true); */
                $addStatusTxt = '';
                if ($sn == 0) {
                    switch ($status) {
                        case RequestForQuote::REQUEST_APPROVED:
                        case RequestForQuote::REQUEST_ACCEPTED_BY_BUYER :
                        case RequestForQuote::REQUEST_DECLINED_BY_SELLER :
                        case RequestForQuote::REQUEST_CANCELLED_BY_BUYER :
                            if ($status != $row['counter_offer_status']) {
                                $addStatusTxt = '<br/>' . $statusArr[$status];
                            }
                            break;
                    }
                }

                $td->appendElement('plaintext', array(), $statusArr[$row[$key]] . '' . $addStatusTxt, true);

                break;
            case 'counter_offer_added_on':
                $td->appendElement('plaintext', array(), FatDate::Format($row[$key]), true);
                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
}

echo $tbl->getHtml();
if (count($arr_listing) == 0) {
    $message = Labels::getLabel('LBL_No_Record_found', $adminLangId);
    $this->includeTemplate('_partial/no-record-found.php', array('adminLangId' => $adminLangId, 'message' => $message));
}