<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="scroll scroll-x js-scrollable table-wrap">
    <?php
    $arr_flds = array(
        'op_invoice_number' => Labels::getLabel('LBL_Invoice_Number', $adminLangId),
        'buyer_name' => Labels::getLabel('LBL_Buyer', $adminLangId),
        'vendor' => Labels::getLabel('LBL_Seller', $adminLangId),
        'charge_status' => Labels::getLabel('LBL_Status', $adminLangId),
        'charge_rental_price' => Labels::getLabel('LBL_Rental_Price', $adminLangId),
        'charge_duration' => Labels::getLabel('LBL_Duration', $adminLangId),
        'charge_amount' => Labels::getLabel('LBL_Penalty', $adminLangId),
        'charge_total_amount' => Labels::getLabel('LBL_Total_Charges(Total_Rental_Price_+_Penalty)', $adminLangId),
        'charge_paid' => Labels::getLabel('LBL_Paid_Charges', $adminLangId),
        'amount_to_pay' => Labels::getLabel('LBL_Pending_Charges', $adminLangId),
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
                case 'vendor':
                    $td->appendElement('plaintext', array(), '<strong>' . Labels::getLabel('LBL_Seller_Name', $adminLangId) . ':  </strong>', true);
                    if ($canViewUsers) {
                        $td->appendElement('a', array('href' => 'javascript:void(0)', 'onClick' => 'redirectfunc("' . UrlHelper::generateUrl('Users') . '", ' . $row['seller_id'] . ')'), $row['seller_name'], true);
                    } else {
                        $td->appendElement('plaintext', array(), $row['seller_name'], true);
                    }
                    $txt = '<br/><strong>' . Labels::getLabel('LBL_Shop', $adminLangId) . ':  </strong>' . "<a href='javascript:void(0)' onclick='redirectfunc(\"" . UrlHelper::generateUrl('Shops') . "\", " . $row['shop_id'] . ")'>" . $row['shop_name'] . "</a>";
                    $txt .= '<br/><strong>' . Labels::getLabel('LBL_User_Name', $adminLangId) . ':  </strong>' . $row['seller_username'];
                    $txt .= '<br/><strong>' . Labels::getLabel('LBL_Email', $adminLangId) . ':   </strong><a href="mailto:' . $row['seller_email'] . '">' . $row['seller_email'] . '</a>';
                    $td->appendElement('plaintext', array(), $txt, true);
                    break;    
                case 'buyer_name':
                    $td->appendElement('plaintext', array(), '<strong>' . Labels::getLabel('LBL_Name', $adminLangId) . ':  </strong>', true);
                    if ($canViewUsers) {
                        $td->appendElement('a', array('href' => 'javascript:void(0)', 'onClick' => 'redirectfunc("' . UrlHelper::generateUrl('Users') . '", ' . $row['buyer_id'] . ')'), $row[$key], true);
                    } else {
                        $td->appendElement('plaintext', array(), $row[$key], true);
                    }
                    $txt = '<br/><strong>' . Labels::getLabel('LBL_User_Name', $adminLangId) . ':  </strong>' . $row['buyer_username'];
                    $txt .= '<br/><strong>' . Labels::getLabel('LBL_Email', $adminLangId) . ':  </strong><a href="mailto:' . $row['buyer_email'] . '">' . $row['buyer_email'] . '</a>';
                    $txt .= '<br/><strong>' . Labels::getLabel('LBL_Phone', $adminLangId) . ':  </strong>' . $row['user_dial_code'] . ' ' . $row['buyer_phone'];
                    $td->appendElement('plaintext', array(), $txt, true);
                    break;
                case 'charge_status':
                    $statusTxt = (isset($statusArr[$row[$key]])) ? $statusArr[$row[$key]] : Labels::getLabel('LBL_Unpaid', $adminLangId);
                    $td->appendElement('span', array(), $statusTxt, true);
                    break;
                case 'charge_duration':
                    $str = $row[$key]. ' '. $rentalDurationType[$row['charge_duration_type']];
                    $td->appendElement('span', array('style' => 'white-space:nowrap;'), $str, true);
                    break;
                case 'action':
                    $td->appendElement('a', array('href' => UrlHelper::generateUrl('SellerOrders', 'view', array($row['op_id'])), 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_View_Order_Detail', $adminLangId)), "<i class='far fa-eye icon'></i>", true);
                    break;
                case 'charge_amount' :
                    $str = Labels::getLabel('LBL_N/A', $adminLangId);
                    if ($row['charge_amount'] > 0) {
                        $str = $row['charge_amount'] . ' %';
                        if ($row['charge_amount_type'] == LateChargesProfile::AMOUNT_TYPE_FIXED) {
                            $str = CommonHelper::displayMoneyFormat($row['charge_amount_type']);
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
    
    if (count($chargesListing) == 0) {
        $tbl->appendElement('tr')->appendElement('td', array('colspan' => count($arr_flds)), Labels::getLabel('LBL_No_Records_Found', $adminLangId));
    }
    echo $tbl->getHtml();
    $postedData['page'] = $page;
    echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmChargesSrchPaging'));
    $pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'pageSize' => $pageSize, 'recordCount' => $recordCount, 'adminLangId' => $adminLangId);
    $this->includeTemplate('_partial/pagination.php', $pagingArr, false);
?>
</div>