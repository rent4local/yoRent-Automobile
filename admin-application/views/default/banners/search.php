<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    'select_all' => Labels::getLabel('LBL_Select_all', $adminLangId),
    'listserial' => Labels::getLabel('LBL_#', $adminLangId),
    'blocation_name' => Labels::getLabel('LBL_Title', $adminLangId),
    /* 'blocation_banner_width' => Labels::getLabel('LBL_Preffered_Width_(in_pixels)', $adminLangId),
    'blocation_banner_height' => Labels::getLabel('LBL_Preffered_Height_(in_pixels)', $adminLangId), */
    'blocation_promotion_cost' => Labels::getLabel('LBL_Promotion_Cost', $adminLangId),
    'blocation_active' => Labels::getLabel('LBL_Status', $adminLangId),
    'action' => '',
);
if (!$canEdit || empty($arr_listing)) {
    unset($arr_flds['select_all'], $arr_flds['action']);
}

$tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table table-responsive table--hovered'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $key => $val) {
    if ('select_all' == $key) {
        $th->appendElement('th')->appendElement('plaintext', array(), '<label class="checkbox"><input title="' . $val . '" type="checkbox" onclick="selectAll( $(this) )" class="selectAll-js"></label>', true);
    } else {
        $e = $th->appendElement('th', array(), $val);
    }
}

$sr_no = $page == 1 ? 0 : $pageSize * ($page - 1);
foreach ($arr_listing as $sn => $row) {
    $sr_no++;
    /* $tr = $tbl->appendElement('tr',array('class' => ($row['blocation_active'] != applicationConstants::ACTIVE) ? 'fat-inactive' : '' )); */
    $tr = $tbl->appendElement('tr', array());
    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'select_all':
                $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="blocation_ids[]" value=' . $row['blocation_id'] . '></label>', true);
                break;
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'blocation_name':
                $td->appendElement('plaintext', array(), $row['blocation_name'], true);
                break;
            case 'blocation_active':
                $active = "";
                if ($row['blocation_active']) {
                    $active = 'checked';
                }
                $statusAct = ($canEdit === true) ? 'toggleStatusBannerLocation(event,this,' . applicationConstants::YES . ')' : 'toggleStatusBannerLocation(event,this,' . applicationConstants::NO . ')';
                $statusClass = ($canEdit === false) ? 'disabled' : '';
                $str = '<label class="statustab -txt-uppercase">
                <input ' . $active . ' type="checkbox" id="switch' . $row['blocation_id'] . '" value="' . $row['blocation_id'] . '" onclick="' . $statusAct . '" class="switch-labels"/>
                <i class="switch-handles ' . $statusClass . '"></i></label>';
                $td->appendElement('plaintext', array(), $str, true);
                break;
            case 'blocation_promotion_cost':
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
            case 'action':
                if ($canEdit) {
                    $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Edit', $adminLangId), "onclick" => "addBannerLocation(" . $row['blocation_id'] . ")"), "<i class='far fa-edit icon'></i>", true);
                    /* $td->appendElement('a', array('href' => 'javascript:void(0)', 'href' => 'javascript:void(0)', 'onClick' => "displayImageInFacebox('" . CONF_WEBROOT_FRONT_URL . "images/admin/banner_layouts/layout-3.jpg');", 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Product_Detail_page_layout', $adminLangId)), "<i class='fas fa-file-image'></i>", true); */
                    $url = UrlHelper::generateUrl('banners', 'listing', array($row['blocation_id']));
                    $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Banners', $adminLangId), 'onclick' => 'redirecrt("' . $url . '")'), "<i class='ion-images icon'></i>", true);
                }
                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
}
if (count($arr_listing) == 0) {
    $tbl->appendElement('tr')->appendElement('td', array('colspan' => count($arr_flds)), Labels::getLabel('LBL_No_Records_Found', $adminLangId));
}

$frm = new Form('frmBannersLocListing', array('id' => 'frmBannersLocListing'));
$frm->setFormTagAttribute('class', 'web_form last_td_nowrap actionButtons-js');
$frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');
$frm->setFormTagAttribute('action', UrlHelper::generateUrl('Banners', 'toggleBulkStatuses'));
$frm->addHiddenField('', 'status');

echo $frm->getFormTag();
echo $frm->getFieldHtml('status');
echo $tbl->getHtml(); ?>
</form>
<?php $postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array(
    'name' => 'frmBannerSearchPaging'
));
$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'adminLangId' => $adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
