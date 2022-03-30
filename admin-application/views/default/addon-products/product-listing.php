<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    'listserial' => '#',
    'shop_name' => Labels::getLabel('LBL_Shop_Name', $adminLangId),
    'selprod_identifier' => Labels::getLabel('LBL_Addons_Name', $adminLangId),
    'selprod_price' => Labels::getLabel('LBL_Price', $adminLangId),
    'selprod_active' => Labels::getLabel('LBL_Status', $adminLangId),
    'action' => ''
);

$tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table'));
$th = $tbl->appendElement('thead')->appendElement('tr', array('class' => ''));
foreach ($arr_flds as $val) {
    $e = $th->appendElement('th', array(), $val);
}

$sr_no = ($page == 1) ? 0 : ($pageSize * ($page - 1));
foreach ($arr_listing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr', array('class' => ''));

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no, true);
                break;
            case 'shop_name':
                $shopName = "<a href='javascript:void(0)' onclick='redirectfunc(\"" . UrlHelper::generateUrl('Shops') . "\", " . $row['shop_id'] . ")'>" . $row['shop_name'] . "</a>";
                $userName = "<a href='javascript:void(0)' onclick='redirectfunc(\"" . UrlHelper::generateUrl('Users') . "\", " . $row['shop_user_id'] . ")'>" . $row['user_name'] . "</a>";
                $td->appendElement('plaintext', array(), $shopName . ' (' . $userName . ')', true);
                break;
            case 'selprod_identifier':
                $td->appendElement('plaintext', array(), $row['selprod_title'], true);
                break;
            case 'selprod_price':
                $td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($row[$key], true, true), true);
                break;
            case 'selprod_active':
                if($row[$key] == 1) {
                    $status = Labels::getLabel('LBL_Active', $adminLangId);
                }else {
                    $status = Labels::getLabel('LBL_In-Active', $adminLangId);
                }
                $td->appendElement('plaintext', array(), $status, true);
                break;      
            case 'action':
                $td->appendElement('a', array('href' => UrlHelper::generateUrl('AddonProducts', 'view', array($row['selprod_id'])),'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_View', $adminLangId)), "<i class='fa fa-eye  icon'></i>", true);
                break; 
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
}
echo $tbl->getHtml();
if (count($arr_listing) == 0) {
    $message = Labels::getLabel('LBL_No_Records_Found', $adminLangId);
    $this->includeTemplate('_partial/no-record-found.php', array('adminLangId' => $adminLangId, 'message' => $message));
}
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmAddonProductSearchPaging'));

$pagingArr = array('pageCount' => $pageCount, 'recordCount' => $recordCount, 'page' => $page, 'callBackJsFunc' => 'goToAddonProductSearchPage', 'adminLangId' => $adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
