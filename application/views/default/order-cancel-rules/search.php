<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    /* 'select_all' => Labels::getLabel('LBL_Select_all', $siteLangId), */
    'listserial' => Labels::getLabel('LBL_#', $siteLangId),
    'ocrule_duration_rang' => Labels::getLabel('LBL_Duration_Range(In_Hours)', $siteLangId),
    'ocrule_refund_amount' => Labels::getLabel('LBL_Refund_Amount(In_Percentage)', $siteLangId),
    'ocrule_active' => Labels::getLabel('LBL_Status', $siteLangId),
    'action' => '',
);

$tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table splPriceList-js table-justified'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $key => $val) {
    if ('select_all' == $key) {
        $th->appendElement('th')->appendElement('plaintext', array(), '<label class="checkbox">
        <input title="' . $val . '" type="checkbox" onclick="selectAll( $(this) )" class="selectAll-js"></label>', true);
    }else if('ocrule_refund_amount' == $key) {
        $th->appendElement('th')->appendElement('plaintext', array(), Labels::getLabel('LBL_Refund_Amount(In_Percentage)', $siteLangId) . '<i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="'. Labels::getLabel('LBL_The_amount_in_percentage_which_will_be_refunded_if_cancellation_hours_fall_between_the_range.', $siteLangId) .'"></i>', true);
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
                if ($row['ocrule_is_default'] != OrderCancelRule::MIN_VALUE && $row['ocrule_is_default'] != OrderCancelRule::MAX_VALUE) {
                    $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="ocrule_ids[]" value=' . $row['ocrule_id'] . '></label>', true);
                }
                break;
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'ocrule_duration_rang':
                if ($row['ocrule_is_default'] == OrderCancelRule::MAX_VALUE) {
                    $row['ocrule_duration_max'] = "Infinity";
                }
                $td->appendElement('plaintext', array(), $row['ocrule_duration_min'] . ' - ' . $row['ocrule_duration_max']);
                break;
            case 'ocrule_active':

                /* $active = "";
                if (applicationConstants::ACTIVE == $row['ocrule_active']) {
                    $active = 'checked';
                }
                $str = '<label class="toggle-switch" for="switch' . $row['ocrule_id'] . '" style="pointer-events:none;"><input ' . $active . ' type="checkbox" value="' . $row['ocrule_id'] . '" id="switch' . $row['ocrule_id'] . '" /><div class="slider round"></div></label>';

                $td->appendElement('plaintext', array(), $str, true); */
                $activeInactiveArr = applicationConstants::getActiveInactiveArr($siteLangId);
                $td->appendElement('plaintext', array(), $activeInactiveArr[$row[$key]], true);
                break;
            case 'action':
                $ul = $td->appendElement("ul", array("class" => "actions actions--centered"), '', true);
                if ($row['ocrule_is_default'] == applicationConstants::NO) {
                    $li = $ul->appendElement('li');
                    $li->appendElement(
                        'a',
                        array(
                            'href' => 'javascript:void(0)', 'class' => '',
                            'title' => Labels::getLabel('LBL_Delete', $siteLangId), "onclick" => "deleteRecord(" . $row['ocrule_id'] . ")"
                        ),
                        '<i class="fa fa-trash"></i>',
                        true
                    );
                }
                $li = $ul->appendElement('li');
                $li->appendElement(
                    'a',
                    array(
                        'href' => 'javascript:void(0)', 'class' => '',
                        'title' => Labels::getLabel('LBL_Edit', $siteLangId), "onclick" => "addEditRuleForm(" . $row['ocrule_id'] . "," . $defaultIsActive . ")"
                    ),
                    '<i class="fa fa-edit"></i>',
                    true
                );
                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
    $sr_no++;
}

$frm = new Form('frmCancelReasonListing', array('id' => 'frmCancelRuleListing'));
$frm->setFormTagAttribute('class', 'web_form last_td_nowrap actionButtons-js');
$frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');
$frm->setFormTagAttribute('action', UrlHelper::generateUrl('OrderCancelRules', 'deleteSelected'));
$frm->addHiddenField('', 'status');
echo $frm->getFormTag();
echo $frm->getFieldHtml('status');
echo $tbl->getHtml();

if (count($arr_listing) == 0) {
    $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId, 'message' => $message));
}
?>
</form>