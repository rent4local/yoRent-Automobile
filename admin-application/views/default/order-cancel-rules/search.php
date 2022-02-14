<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    'select_all' => Labels::getLabel('LBL_Select_all', $adminLangId),
    'listserial' => Labels::getLabel('LBL_#', $adminLangId),
    'ocrule_duration_rang' => Labels::getLabel('LBL_Duration_Range(In_Hours)', $adminLangId),
    // 'ocrule_duration_max' => Labels::getLabel('LBL_Duration_Max(In_Hours)', $adminLangId),
    'ocrule_refund_amount' => Labels::getLabel('LBL_Refund_Amount(In_Percentage)', $adminLangId),
    'action' => '',
);
if (!$canEdit) {
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
$sr_no = 1;
foreach ($arr_listing as $sn => $row) {
    $tr = $tbl->appendElement('tr');
    $tr->setAttribute("id", $row['ocrule_id']);
    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'select_all':
                if($row['ocrule_is_default'] != OrderCancelRule::MIN_VALUE && $row['ocrule_is_default'] != OrderCancelRule::MAX_VALUE){
                    $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="ocrule_ids[]" value=' . $row['ocrule_id'] . '></label>', true);
                }
                break;
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'ocrule_duration_rang':
                if($row['ocrule_is_default'] == OrderCancelRule::MAX_VALUE){
                    $row['ocrule_duration_max'] = "Infinity";
                }
                $td->appendElement('plaintext', array(), $row['ocrule_duration_min'].' - '.$row['ocrule_duration_max']);
                break;
            case 'action':
                if ($canEdit) {
                    $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Edit', $adminLangId), "onclick" => "addEditRuleForm(" . $row['ocrule_id'] . ")"), "<i class='far fa-edit icon'></i>", true);
                    if($row['ocrule_is_default'] == applicationConstants::NO){
                        $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Delete', $adminLangId), "onclick" => "deleteRecord(" . $row['ocrule_id'] . ")"), "<i class='fa fa-trash  icon'></i>", true);
                    }
                    
                }
                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
    $sr_no++;
}
if (count($arr_listing) == 0) {
    $tbl->appendElement('tr')->appendElement('td', array('colspan' => count($arr_flds)), Labels::getLabel('LBL_No_Records_Found', $adminLangId));
}

$frm = new Form('frmCancelReasonListing', array('id' => 'frmCancelRuleListing'));
$frm->setFormTagAttribute('class', 'web_form last_td_nowrap actionButtons-js');
$frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');
$frm->setFormTagAttribute('action', UrlHelper::generateUrl('OrderCancelRules', 'deleteSelected'));
$frm->addHiddenField('', 'status');
echo $frm->getFormTag();
echo $frm->getFieldHtml('status');
echo $tbl->getHtml();
?>
</form>