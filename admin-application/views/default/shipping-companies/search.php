<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    'select_all' => Labels::getLabel('LBL_Select_all', $adminLangId),
    'listserial' => Labels::getLabel('LBL_#', $adminLangId),
    'scompany_identifier' => Labels::getLabel('LBL_Shipping_Company', $adminLangId),
    'scompany_active' => Labels::getLabel('LBL_Status', $adminLangId),
    'action' => '',
);
if (!$canEdit) {
    unset($arr_flds['dragdrop']);
    unset($arr_flds['select_all']);
    unset($arr_flds['action']);
}
$tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table table--hovered table-responsive', 'id' => 'shippingCompany'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $key => $val) {
    if ('select_all' == $key) {
        $th->appendElement('th')->appendElement('plaintext', array(), '<label class="checkbox"><input title="' . $val . '" type="checkbox" onclick="selectAll( $(this) )" class="selectAll-js"></label>', true);
    } else {
        $e = $th->appendElement('th', array(), $val);
    }
}

$sr_no = 0;
foreach ($arr_listing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr', array());
    $tr->setAttribute("id", $row['scompany_id']);

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'dragdrop':
                if ($row['scompany_active'] == applicationConstants::ACTIVE) {
                    $td->appendElement('i', array('class' => 'ion-arrow-move icon'));
                    $td->setAttribute("class", 'dragHandle');
                }
                break;
            case 'select_all':
                $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="scompany_ids[]" value=' . $row['scompany_id'] . '></label>', true);
                break;
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'scompany_active':
                $active = "active";
                if (!$row['scompany_active']) {
                    $active = '';
                }
                $statucAct = ($canEdit === true) ? 'toggleStatus(this)' : '';
                $str = '<label id="' . $row['scompany_id'] . '" class="statustab ' . $active . '" onclick="' . $statucAct . '">
                <span data-off="' . Labels::getLabel('LBL_Active', $adminLangId) . '" data-on="' . Labels::getLabel('LBL_Inactive', $adminLangId) . '" class="switch-labels"></span>
                <span class="switch-handles"></span>
                </label>';
                $td->appendElement('plaintext', array(), $str, true);
                break;
            case 'scompany_identifier':
                if ($row['scompany_name'] != '') {
                    $td->appendElement('plaintext', array(), $row['scompany_name'], true);
                    $td->appendElement('br', array());
                    $td->appendElement('plaintext', array(), '(' . $row[$key] . ')', true);
                } else {
                    $td->appendElement('plaintext', array(), $row[$key], true);
                }
                break;
            case 'action':
                if ($canEdit) {
                    $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Edit', $adminLangId), "onclick" => "editForm(" . $row['scompany_id'] . ")"), "<i class='far fa-edit icon'></i>", true);
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

$frm = new Form('frmShpCompListing', array('id' => 'frmShpCompListing'));
$frm->setFormTagAttribute('class', 'web_form last_td_nowrap actionButtons-js');
$frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');
$frm->setFormTagAttribute('action', UrlHelper::generateUrl('ShippingCompanies', 'toggleBulkStatuses'));
$frm->addHiddenField('', 'status');

echo $frm->getFormTag();
echo $frm->getFieldHtml('status');
echo $tbl->getHtml(); ?>
</form>