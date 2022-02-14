<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>

<?php

$arr_flds = array(
    'counter_offer_added_on' => Labels::getLabel('LBL_Date', $siteLangId),
    'counter_offer_total_cost' => Labels::getLabel('LBL_Product_Total_Cost', $siteLangId),
    'counter_offer_rental_security' => Labels::getLabel('LBL_Rental_Security', $siteLangId),
    'counter_offer_shipping_cost' => Labels::getLabel('LBL_Shipping_Cost', $siteLangId),
    'counter_offer_status' => Labels::getLabel('LBL_Status', $siteLangId),
    'counter_offer_comment' => Labels::getLabel('LBL_comment', $siteLangId),
    'action' => /*Labels::getLabel('LBL_Action', $siteLangId)*/ '',
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
            case 'counter_offer_total_cost':
            case 'counter_offer_shipping_cost':
            case 'counter_offer_rental_security':
                $td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($row[$key], true, true), true);
                break;
            case 'action':
                $html = '<ul class="actions">';
                if ($sn == 0 && $canEdit) {
                    switch ($status) {
                        case RequestForQuote::REQUEST_QUOTED:
                            $html .= '<li><a  href="javascript:void(0)" onclick="changeStatus(' . $rfqId . ', ' . RequestForQuote::REQUEST_DECLINED_BY_SELLER . ')"><span data-toggle="tooltip" data-placement="top" title="" data-original-title="' . Labels::getLabel('LBL_Close_Request', $siteLangId) . '"><i class="fa fa-times"></i></a></li>';
                            break;
                        case RequestForQuote::REQUEST_COUNTER_BY_BUYER:
                            $html .= '<li><a href="javascript:void(0)" onclick="changeStatus(' . $rfqId . ', ' . RequestForQuote::REQUEST_DECLINED_BY_SELLER . ')"><span data-toggle="tooltip" data-placement="top" title="" data-original-title="' . Labels::getLabel('LBL_Close_Request', $siteLangId) . '"><i class="fa fa-times"></i></a></li>';
                            $html .= '<li><a href="javascript:void(0)" onclick="changeStatus(' . $rfqId . ', ' . RequestForQuote::REQUEST_APPROVED . ')"><span data-toggle="tooltip" data-placement="top" title="" data-original-title="' . Labels::getLabel('LBL_Accept_Offer', $siteLangId) . '"><i class="fa fa-check-circle" ></i></a></li>';
                            $html .= '<li><a href="javascript:void(0)" onclick="counterOfferForm(' . $rfqId . ')"><span data-toggle="tooltip" data-placement="top" title="" data-original-title="' . Labels::getLabel('LBL_Counter_Offer', $siteLangId) . '"><i class="fa fa-gift" ></i>';
                            break;
                        case RequestForQuote::REQUEST_ACCEPTED_BY_BUYER:
                            $html .= '<li><a href="javascript:void(0)" onclick="changeStatus(' . $rfqId . ', ' . RequestForQuote::REQUEST_DECLINED_BY_SELLER . ')"><span data-toggle="tooltip" data-placement="top" title="" data-original-title="' . Labels::getLabel('LBL_Close_Request', $siteLangId) . '"><i class="fa fa-times" ></i></a></li>';
                            $html .= '<li><a href="javascript:void(0)" onclick="changeStatus(' . $rfqId . ', ' . RequestForQuote::REQUEST_APPROVED . ')"><span data-toggle="tooltip" data-placement="top" title="" data-original-title="' . Labels::getLabel('LBL_Confirm_Order', $siteLangId) . '"><i class="fa fa-check-circle" ></i></a></li>';
                            break;
                        case RequestForQuote::REQUEST_COUNTER_BY_SELLER:
                            $html .= '<li><a href="javascript:void(0)" onclick="changeStatus(' . $rfqId . ', ' . RequestForQuote::REQUEST_DECLINED_BY_SELLER . ')"><span data-toggle="tooltip" data-placement="top" title="" data-original-title="' . Labels::getLabel('LBL_Close_Request', $siteLangId) . '"><i class="fa fa-times" ></i></a></li>';

                            break;
                    }
                }
                $html .= '</ul>';

                $td->appendElement('plaintext', array(), $html, true);
                /* $td->appendElement(
                  'a',
                  array('href'=>'', 'class'=>'btn btn--primary btn--sm', 'title'=>Labels::getLabel('LBL_View', $siteLangId)),
                  Labels::getLabel('LBL_View', $siteLangId),
                  true
                  );
                  $td->appendElement(
                  'a',
                  array('href'=>'', 'class'=>'btn btn--primary btn--sm', 'title'=>Labels::getLabel('LBL_Counter_Offer', $siteLangId)),
                  Labels::getLabel('LBL_Counter_Offer', $siteLangId),
                  true
                  ); */
                break;
            case 'counter_offer_status':
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
    $message = Labels::getLabel('LBL_No_Record_found', $siteLangId);
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId, 'message' => $message));
}    

