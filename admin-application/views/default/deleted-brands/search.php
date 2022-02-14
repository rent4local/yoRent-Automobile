<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if (count($arr_listing) == 0) {
    $this->includeTemplate('_partial/no-record-found.php', array('adminLangId' => $adminLangId));
}else{
$arr_flds = array(
    'listserial' => Labels::getLabel('LBL_#', $adminLangId),
    'brand_logo' => Labels::getLabel('LBL_Logo', $adminLangId),
    'brand_identifier' => Labels::getLabel('LBL_Brand_Name', $adminLangId),
    'action' => '',
);

$tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table table-responsive table--hovered text-center'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $key => $val) {
    $th->appendElement('th', array(), $val);
}

$sr_no = ($page > 1) ? $recordCount - (($page - 1) * $pageSize) : $recordCount;
foreach ($arr_listing as $sn => $row) {
    $tr = $tbl->appendElement('tr');
    $tr->setAttribute("id", $row['brand_id']);

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'brand_logo':
                $td->appendElement(
                    'plaintext',
                    array('style' => 'text-align:center'),
                    '<img class="max-img" style="margin:0 auto;" src="' . UrlHelper::generateUrl('image', 'brand', array($row['brand_id'], $adminLangId, 'MINITHUMB'), CONF_WEBROOT_FRONT_URL) . '">',
                    true
                );
                break;
            case 'brand_identifier':
                if ($row['brand_name'] != '') {
                    $td->appendElement('plaintext', array(), $row['brand_name'], true);
                    $td->appendElement('br', array());
                    $td->appendElement('plaintext', array(), '(' . $row[$key] . ')', true);
                } else {
                    $td->appendElement('plaintext', array(), $row[$key], true);
                }
                break;
            case 'action':
                if ($canEdit) {
                    $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Restore_Brand', $adminLangId), "onclick" => "restoreBrand(" . $row['brand_id'] . ")"), '<i class="fas fa-recycle"></i>', true);
                }
                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
    $sr_no--;
}

$frm = new Form('frmBrandListing', array('id' => 'frmBrandListing'));
$frm->setFormTagAttribute('class', 'web_form last_td_nowrap actionButtons-js');
$frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');

echo $frm->getFormTag();
echo $tbl->getHtml(); ?>
</form>
<?php $postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array(
    'name' => 'frmBrandSearchPaging'
));
$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'adminLangId' => $adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
}
