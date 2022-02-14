<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="scroll scroll-x js-scrollable table-wrap">
    <?php
    $arr_flds = array(
        'op_invoice_number' => Labels::getLabel('LBL_Invoice_Number', $adminLangId),
        'buyer_name' => Labels::getLabel('LBL_Buyer', $adminLangId),
        'order_amount' => Labels::getLabel('LBL_Total_Amount', $adminLangId),
        'refundable_amount' => Labels::getLabel('LBL_Refundable_Amount', $adminLangId),
        'earning' => Labels::getLabel('LBL_Seller_Earning', $adminLangId),
        'commission' => Labels::getLabel('LBL_Commission', $adminLangId),
        'rental_details' => Labels::getLabel('LBL_Details', $adminLangId),
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
            $orderTotalAmount = CommonHelper::orderProductAmount($row, 'netamount', false, User::USER_TYPE_SELLER);
            
            
            $netAmount = $orderTotalAmount;
            $shippingCharges = $row['shipping_charges'];
            $orderTotalAmount = CommonHelper::orderProductAmount($row, 'netamount') - ($row['opd_rental_security'] * $row['op_qty']) - $shippingCharges;
            $refunableAmount = ($orderTotalAmount * $row['ocrequest_refund_amount'] / 100) + $shippingCharges + ($row['opd_rental_security'] * $row['op_qty']);
            
            switch ($key) {
                case 'op_invoice_number':
                    $td->appendElement('plaintext', array(), '#' . $row['op_invoice_number'], true);
                    break;
                case 'action':
                    $td->appendElement('a', array('href' => UrlHelper::generateUrl('SellerOrders', 'view', array($row['op_id'])), 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_View_Order_Detail', $adminLangId)), "<i class='far fa-eye icon'></i>", true);
                    break;
                case 'refundable_amount' :
                    $breakpointsHtml = '<div style=\'min-width:300px;\'><span>'. Labels::getLabel('LBL_Security_Amont', $adminLangId) .' : '. CommonHelper::displayMoneyFormat($row['opd_rental_security'] * $row['op_qty']) .'</span><br><span>'. Labels::getLabel('LBL_Shipping_Charges', $adminLangId) .' :  '. CommonHelper::displayMoneyFormat($shippingCharges) .'</span><br /><span>'. Labels::getLabel('LBL_Rental_Amount', $adminLangId) .'('. $row['ocrequest_refund_amount'].'%) : '. CommonHelper::displayMoneyFormat($orderTotalAmount * $row['ocrequest_refund_amount'] / 100) .'</span></div>';
                
                    $str = CommonHelper::displayMoneyFormat($refunableAmount). ' <span><i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="" data-original-title="'. $breakpointsHtml .'" data-html="true"></i></span>';
                    $td->appendElement('span', array(), $str, true);
                    break;
                case 'earning' :
                    $str = CommonHelper::displayMoneyFormat($netAmount - $refunableAmount);
                    $td->appendElement('span', array(), $str, true);
                    break; 
                case 'commission' :   
                    $commission = ($netAmount - $refunableAmount) * $row['op_commission_percentage'] / 100;
                    if ($commission > 0) {
                        $str = '<a href="javascript:void(0);" data-toggle="tooltip" data-placement="right" data-original-title="'. $row['op_commission_percentage'] .'%" >'. CommonHelper::displayMoneyFormat($commission). '</a>';
                    } else {
                        $str = CommonHelper::displayMoneyFormat($commission);
                    }
                    
                    
                    $td->appendElement('span', array(), $str, true);
                    break; 
                    
                case 'rental_details' : 
                    $str = sprintf(Labels::getLabel('LBL_Order_rental_start_date_%s', $adminLangId), FatDate::format($row['opd_rental_start_date'], true));
                    $str .= '<br>' . sprintf(Labels::getLabel('LBL_Order_Cancel_Before_%s_hours', $adminLangId), $row['ocrequest_hours_before_rental']);
                    $td->appendElement('plaintext', array(), $str, true);
                    break;
                case 'order_amount' :
                    $str = CommonHelper::displayMoneyFormat($netAmount);
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
    echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmPenaltySrchPaging'));
    $pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'pageSize' => $pageSize, 'recordCount' => $recordCount, 'adminLangId' => $adminLangId);
    $this->includeTemplate('_partial/pagination.php', $pagingArr, false);
?>
</div>

<script>
$(function () {
  $('[data-toggle="tooltip"]').tooltip()
})    
</script>