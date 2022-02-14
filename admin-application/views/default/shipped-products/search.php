<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$arr_flds = array(
    'listserial'    =>    Labels::getLabel('LBL_#', $adminLangId),
    'product_name' => Labels::getLabel('LBL_product_name', $adminLangId),
    'total_seller_ship' => Labels::getLabel('LBL_Shipped_By_Seller', $adminLangId),
    'total_admin_seller_ship' => Labels::getLabel('LBL_Shipped_By_Admin', $adminLangId),
    'shipprofile_name' => Labels::getLabel('LBL_shipping_profile', $adminLangId),
    'action' => '',
);
$tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table table--hovered table-responsive'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $val) {
    $e = $th->appendElement('th', array(), $val);
}

$sr_no = ($page > 1) ? $recordCount - (($page - 1) * $pageSize) : $recordCount;
foreach ($arrListing as $sn => $row) {
    $tr = $tbl->appendElement('tr');
    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'total_seller_ship':
                if($row[$key] > 0) {
                    $td->appendElement('a', array('href' => 'javascript:void(0)', "onclick" => "viewSellerShip(" . $row['shippro_product_id'] . ")"), $row[$key], true);
                }else {
                    $td->appendElement('plaintext', array(), $row[$key], true);
                }
                break;
            case 'total_admin_seller_ship':
                if($row[$key] > 0) {
                    $td->appendElement('a', array('href' => 'javascript:void(0)', "onclick" => "viewAdminSellerShip(" . $row['shippro_product_id'] . ")"), $row[$key], true);
                }else {
                    $td->appendElement('plaintext', array(), $row[$key], true);
                }
                break;
            case 'action':
                if($canEdit) {
                    $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Change_Status', $adminLangId), "onclick" => "updateProductsShipping(" . $row['shippro_product_id'] . ", " . $row['shippro_shipprofile_id'] . ")"), '<i class="fas fa-toggle-off"></i>', true);
                }
                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
    $sr_no--;
}
if (count($arrListing) == 0) {
    $tbl->appendElement('tr')->appendElement('td', array('colspan' => count($arr_flds)), Labels::getLabel('LBL_No_Records_Found', $adminLangId));
}
echo $tbl->getHtml();
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array(
    'name' => 'frmShippedProductsPaging'
));
$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'pageSize' => $pageSize, 'recordCount' => $recordCount, 'adminLangId' => $adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
