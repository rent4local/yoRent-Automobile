<?php

defined('SYSTEM_INIT') or die('Invalid Usage.');
if ($productFor == applicationConstants::PRODUCT_FOR_SALE) {
    $qtyLabel = Labels::getLabel('LBL_Sold_Quantity', $adminLangId);
} else {    
    $qtyLabel = Labels::getLabel('LBL_Rented_Quantity', $adminLangId);
}

$arrFlds = array(
    'prodcat_name' => Labels::getLabel('LBL_Category', $adminLangId),
    'totSoldQty' => $qtyLabel,
    'totRefundedQty' => Labels::getLabel('LBL_Refunded_Quantity', $adminLangId),
    'cancelledOrderQty' => Labels::getLabel('LBL_Cancelled_Orders_Qty', $adminLangId),
    /* $rowKey => $qtyLabel, */
    'wishlistUserCounts' => Labels::getLabel('LBL_WishList_User_Counts', $adminLangId)
);

$tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table table-responsive table--hovered'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arrFlds as $val) {
    $e = $th->appendElement('th', array(), $val, true);
}

$sr_no = 0;
foreach ($arr_listing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr', array('class' => ($row['prodcat_active'] != applicationConstants::ACTIVE || $row['prodcat_deleted'] == applicationConstants::YES) ? 'fat-inactive' : '', 'title' => ($row['prodcat_active'] != applicationConstants::ACTIVE || $row['prodcat_deleted'] == applicationConstants::YES) ? 'In-Active Or Record Deleted' : ''));

    foreach ($arrFlds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;

            case 'prodcat_name':
                $td->appendElement('plaintext', array(), $catTreeAssocArr[$row['prodcat_id']], true);
                break;

            case 'followers':
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;

            case 'totSoldQty':
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;

            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
}
if (count($arr_listing) == 0) {
    $tbl->appendElement('tr')->appendElement('td', array(
        'colspan' => count($arrFlds)), Labels::getLabel('LBL_No_Records_Found', $adminLangId)
    );
}
echo $tbl->getHtml();

$reprtHeadingLbl = ($reportType == 1) ? Labels::getLabel('LBL_Top_Categories_Report', $adminLangId) : Labels::getLabel('LBL_Bad_Categories_Report', $adminLangId);
?>

<script>
$(document).ready(function(e) {
    $('.report-heading--js').text('<?php echo $reprtHeadingLbl; ?>');
});    
</script>