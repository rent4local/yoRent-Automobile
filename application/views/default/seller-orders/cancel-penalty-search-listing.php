<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="scroll scroll-x js-scrollable table-wrap">
    <?php
    $arr_flds = array(
        'op_invoice_number' => Labels::getLabel('LBL_Invoice_Number', $siteLangId),
        'buyer_name' => Labels::getLabel('LBL_Buyer', $siteLangId),
        'order_amount' => Labels::getLabel('LBL_Total_Amount', $siteLangId),
        'refundable_amount' => Labels::getLabel('LBL_Refundable_Amount', $siteLangId),
        'earning' => Labels::getLabel('LBL_Earning', $siteLangId),
        'rental_details' => Labels::getLabel('LBL_Details', $siteLangId),
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
            $orderTotalAmount = CommonHelper::orderProductAmount($row, 'netamount');
            $netAmount = $orderTotalAmount;
            $shippingCharges = $row['shipping_charges'];
            $orderTotalAmount = CommonHelper::orderProductAmount($row, 'netamount') - ($row['opd_rental_security'] * $row['op_qty']) - $shippingCharges;
            $refunableAmount = ($orderTotalAmount * $row['ocrequest_refund_amount'] / 100) + $shippingCharges + ($row['opd_rental_security'] * $row['op_qty']);
            
            switch ($key) {
                case 'op_invoice_number':
                    $td->appendElement('plaintext', array(), '#' . $row['op_invoice_number'], true);
                    break;
                case 'action':
                    $ul = $td->appendElement("ul", array("class" => "actions"), '', true);
                    $li = $ul->appendElement("li");
                    $li->appendElement('a', array('href' => UrlHelper::generateUrl('SellerOrders', 'viewOrder', array($row['op_id'])), 'class' => '', 'title' => Labels::getLabel('LBL_View_Order_Details', $siteLangId)), '<i class="fa fa-eye"></i>', true
                    );
                    break;
                case 'refundable_amount' :
                    $breakpointsHtml = '<div style=\'min-width:300px;\'><span>'. Labels::getLabel('LBL_Security_Amont', $siteLangId) .' : '. CommonHelper::displayMoneyFormat($row['opd_rental_security'] * $row['op_qty']) .'</span><br><span>'. Labels::getLabel('LBL_Shipping_Charges', $siteLangId) .' :  '. CommonHelper::displayMoneyFormat($shippingCharges) .'</span><br /><span>'. Labels::getLabel('LBL_Rental_Amount', $siteLangId) .'('. $row['ocrequest_refund_amount'].'%) : '. CommonHelper::displayMoneyFormat($orderTotalAmount * $row['ocrequest_refund_amount'] / 100) .'</span></div>';
                
                    $str = CommonHelper::displayMoneyFormat($refunableAmount). ' <span><i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="" data-original-title="'. $breakpointsHtml .'" data-html="true"></i></span>';
                    $td->appendElement('span', array(), $str, true);
                    break;
                case 'earning' :
                    $str = CommonHelper::displayMoneyFormat($netAmount - $refunableAmount);
                    $td->appendElement('span', array(), $str, true);
                    break;  
                case 'rental_details' : 
                    $str = sprintf(Labels::getLabel('LBL_Order_rental_start_date_%s', $siteLangId), FatDate::format($row['opd_rental_start_date'], true));
                    $str .= '<br>' . sprintf(Labels::getLabel('LBL_Order_Cancel_Before_%s_hours', $siteLangId), $row['ocrequest_hours_before_rental']);
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
    echo $tbl->getHtml();
    if (count($chargesListing) == 0) {
        $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
        $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId, 'message' => $message));
    }
    ?>
</div>
<?php
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmPenaltySrchPaging'));
$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'callBackJsFunc' => 'goToChargesSearchPage', 'siteLangId' => $siteLangId, 'pageSize' => $pageSize, 'removePageCentClass' => true);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
