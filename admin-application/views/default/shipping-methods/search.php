<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    'dragdrop' => '',
    'select_all' => Labels::getLabel('LBL_Select_all', $adminLangId),
    'listserial' => Labels::getLabel('LBL_#', $adminLangId),
    'shippingapi_identifier' => Labels::getLabel('LBL_Shipping_Method', $adminLangId),
    'shippingapi_active' => Labels::getLabel('LBL_Status', $adminLangId),
    'action' => '',
);
if (!$canEdit) {
    unset($arr_flds['dragdrop']);
    unset($arr_flds['select_all']);
    unset($arr_flds['action']);
}
$tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table table--hovered table-responsive ', 'id' => 'shippingMethod'));
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
    $tr->setAttribute("id", $row['shippingapi_id']);

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'dragdrop':
                if ($row['shippingapi_active'] == applicationConstants::ACTIVE) {
                    $td->appendElement('i', array('class' => 'ion-arrow-move icon'));
                    $td->setAttribute("class", 'dragHandle');
                }
                break;
            case 'select_all':
                $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="shippingapi_ids[]" value=' . $row['shippingapi_id'] . '></label>', true);
                break;
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'shippingapi_active':
                $active = "active";
                if (!$row['shippingapi_active']) {
                    $active = '';
                }
                $statucAct = ($canEdit === true) ? 'toggleStatus(this)' : '';
                $str = '<label id="' . $row['shippingapi_id'] . '" class="statustab ' . $active . '" onclick="' . $statucAct . '">
                <span data-off="' . Labels::getLabel('LBL_Active', $adminLangId) . '" data-on="' . Labels::getLabel('LBL_Inactive', $adminLangId) . '" class="switch-labels"></span>
                <span class="switch-handles"></span>
                </label>';
                $td->appendElement('plaintext', array(), $str, true);
                break;
            case 'shippingapi_identifier':
                if ($row['shippingapi_name'] != '') {
                    $td->appendElement('plaintext', array(), $row['shippingapi_name'], true);
                    $td->appendElement('br', array());
                    $td->appendElement('plaintext', array(), '(' . $row[$key] . ')', true);
                } else {
                    $td->appendElement('plaintext', array(), $row[$key], true);
                }
                break;
            case 'action':
                if ($canEdit) {
                    $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Edit', $adminLangId), "onclick" => "editForm(" . $row['shippingapi_id'] . ")"), "<i class='far fa-edit icon'></i>", true);
                    if ($row['shippingapi_id'] > 1) {
                        $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Settings', $adminLangId), "onclick" => "settingsForm(" . $row['shippingapi_id'] . ")"), "<i class='ion-settings icon'></i>", true);
                    }
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

$frm = new Form('frmShpApiListing', array('id' => 'frmShpApiListing'));
$frm->setFormTagAttribute('class', 'web_form last_td_nowrap actionButtons-js');
$frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');
$frm->setFormTagAttribute('action', UrlHelper::generateUrl('ShippingMethods', 'toggleBulkStatuses'));
$frm->addHiddenField('', 'status');

echo $frm->getFormTag();
echo $frm->getFieldHtml('status');
echo $tbl->getHtml(); ?>
</form>
<script>
    $(document).ready(function() {
        $('#shippingMethod').tableDnD({
            onDrop: function(table, row) {
                fcom.displayProcessing();
                var order = $.tableDnD.serialize('id');
                fcom.ajax(fcom.makeUrl('ShippingMethods', 'updateOrder'), order, function(res) {
                    var ans = $.parseJSON(res);
                    if (ans.status == 1) {
                        fcom.displaySuccessMessage(ans.msg);
                    } else {
                        fcom.displayErrorMessage(ans.msg);
                    }
                });
            },
            dragHandle: ".dragHandle",
        });
    });
</script>