<?php

defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    'listserial' => Labels::getLabel('LBL_Sr_No.', $siteLangId),
    'selprod_identifier' => Labels::getLabel('LBL_Rental_Addons', $siteLangId),
    'selprod_price' => Labels::getLabel('LBL_Price', $siteLangId),
    'selprod_active' => Labels::getLabel('LBL_Status', $siteLangId),
    'action' => '',
);
if (!$canEdit) {
    unset($arr_flds['action']);
}

$tableClass = '';
    if (0 < count($arr_listing)) {
        $tableClass = "table-justified";
    }
    $tbl = new HtmlElement('table', array('class' => 'table ' . $tableClass));

/* $tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table')); */
$th = $tbl->appendElement('thead')->appendElement('tr', array('class' => ''));
foreach ($arr_flds as $val) {
    $e = $th->appendElement('th', array(), $val);
}
$activeInacArr = applicationConstants::getActiveInactiveArr($siteLangId);


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
            case 'selprod_identifier':
                $td->appendElement('plaintext', array(), $row['selprod_title'] . '<br>', true);
                //$td->appendElement('plaintext', array(), '(' . $row[$key] . ')', true);
                break;
            case 'selprod_price':
                $td->appendElement('plaintext', array(), CommonHelper::displayMoneyFormat($row[$key], true, true), true);
                break;

            case 'selprod_active':
                $active = "";
                if (0 < $row[$key]) {
                    $active = 'checked';
                }
                if ($canEdit) {
                    $status = ($row[$key] + 1) % 2;
                    $str = '<label class="toggle-switch" for="switch' . $row['selprod_id'] . '"><input ' . $active . ' type="checkbox" id="switch' . $row['selprod_id'] . '" onclick="changeStatus(' . $row['selprod_id'] . ', ' . $status . ')"/><div class="slider round"></div></label>';
                    $td->appendElement('plaintext', array(), $str, true);
                } else {
                    $td->appendElement('plaintext', array(), $activeInacArr[$row[$key]], true);
                }
                
                break;
            case 'action':
                $ul = $td->appendElement("ul", array('class' => 'actions'), '', true);
                $li = $ul->appendElement("li");
                $li->appendElement(
                'a', array('href' => UrlHelper::generateUrl('AddonProducts', 'form', [$row['selprod_id']]), 'class' => '', 'title' => Labels::getLabel('LBL_Edit', $siteLangId), true), '<i class="fa fa-edit"></i>', true
                );
                $li = $ul->appendElement("li");
                $li->appendElement(
                'a', array('href' => "javascript:void(0)", 'onclick' => "attachAddonForm(". $row['selprod_id'] .");",  'class' => '', 'title' => Labels::getLabel('LBL_Attach_With_Product', $siteLangId), true), '<i class="fa fa-link"></i>', true
                );
                

                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
}
echo $tbl->getHtml();
if (count($arr_listing) == 0) {
    $message = Labels::getLabel('LBL_No_result_found', $siteLangId);
    $linkArr = array();
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId, 'linkArr' => $linkArr, 'message' => $message));
}


$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmAddonProductSearchPaging'));
$pagingArr = array('recordCount' => $recordCount, 'pageCount' => $pageCount, 'page' => $page, 'callBackJsFunc' => 'goToAddonProductSearchPage', 'siteLangId' => $siteLangId, 'pageSize' => $pageSize, 'removePageCentClass' => true);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
