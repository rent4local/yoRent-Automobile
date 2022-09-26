<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="scroll scroll-x js-scrollable table-wrap">
    <?php
    $arr_flds = array(
        'op_invoice_number' => Labels::getLabel('LBL_Invoice_Number', $siteLangId),
        'shop_name' => Labels::getLabel('LBL_Shop', $siteLangId),
        'charge_status' => Labels::getLabel('LBL_Status', $siteLangId),
        'charge_rental_price' => Labels::getLabel('LBL_Rental_Price', $siteLangId),
        'charge_duration' => Labels::getLabel('LBL_Duration', $siteLangId),
        'charge_amount' => Labels::getLabel('LBL_Penalty', $siteLangId),
        'charge_total_amount' => Labels::getLabel('LBL_Total_Charges(Total_Rental_Price_+_Penalty)', $siteLangId),
        'charge_paid' => Labels::getLabel('LBL_Paid_Charges', $siteLangId),
        'amount_to_pay' => Labels::getLabel('LBL_Pending_Charges', $siteLangId),
        'action' => '',
    );
    $tableClass = '';
    if (0 < count($chargesListing)) {
        $tableClass = "table-justified";
    }
    $tbl = new HtmlElement('table', array('class' => 'table ' . $tableClass));
    $th = $tbl->appendElement('thead')->appendElement('tr', array('class' => ''));
    foreach ($arr_flds as $val) {
        $e = $th->appendElement('th', array(), $val);
    }

    $sr_no = 0;
    foreach ($chargesListing as $sn => $row) {
        $sr_no++;
        $tr = $tbl->appendElement('tr', array('class' => ''));

        foreach ($arr_flds as $key => $val) {
            $td = $tr->appendElement('td');
            switch ($key) {
                case 'op_invoice_number':
                    $td->appendElement('plaintext', array(), '#' . $row['op_invoice_number'], true);
                    break;

                case 'charge_total_amount':
                case 'charge_rental_price':
                case 'charge_paid':
                    $txt = '';
                    $txt .= CommonHelper::displayMoneyFormat($row[$key]);
                    $td->appendElement('plaintext', array(), $txt, true);
                    break;
                case 'amount_to_pay' :
                    $pendingAmount = $row['charge_total_amount'] - $row['charge_paid'];
                    $txt = CommonHelper::displayMoneyFormat($pendingAmount);
                    $td->appendElement('plaintext', array(), $txt, true);
                    break;
                case 'charge_status':
                    $statusTxt = (isset($statusArr[$row[$key]])) ? $statusArr[$row[$key]] : Labels::getLabel('LBL_Unpaid', $siteLangId);
                    $td->appendElement('span', array(), $statusTxt, true);
                    break;
                case 'charge_duration':
                    $str = $row[$key]. ' '. $rentalDurationType[$row['charge_duration_type']];
                    $td->appendElement('span', array('style' => 'white-space:nowrap;'), $str, true);
                    break;
                case 'action':
                    $ul = $td->appendElement("ul", array("class" => "actions"), '', true);
                    $li = $ul->appendElement("li");
                    $li->appendElement(
                            'a', array(
                        'href' => UrlHelper::generateUrl('Buyer', 'viewOrder', array($row['op_order_id'], $row['op_id'])), 'class' => '',
                        'title' => Labels::getLabel('LBL_View_Order_Details', $siteLangId)
                            ), '<i class="fa fa-eye"></i>', true
                    );
                    break;
                case 'charge_amount' :
                    $str = Labels::getLabel('LBL_N/A', $siteLangId);
                    if ($row['charge_amount'] > 0) {
                        $str = $row['charge_amount'] . ' %';
                        if ($row['charge_amount_type'] == LateChargesProfile::AMOUNT_TYPE_FIXED) {
                            $str = CommonHelper::displayMoneyFormat($row['charge_amount']);
                        }
                    }
                    $td->appendElement('span', array(), $str, true);
                    break;
                default:
                    $td->appendElement('plaintext', array(), $row[$key], true);
                    break;
            }
        }
    }
    echo $tbl->getHtml();
    if (count($chargesListing) == 0) {
        $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
        $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId, 'message' => $message));
    }
    ?>
</div>
<?php
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmChargesSrchPaging'));
$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'callBackJsFunc' => 'goToChargesSearchPage', 'siteLangId' => $siteLangId, 'pageSize' => $pageSize, 'removePageCentClass' => true);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
