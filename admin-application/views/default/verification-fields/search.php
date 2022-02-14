<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    'listserial' => Labels::getLabel('LBL_#', $adminLangId),
    'vflds_identifier' => Labels::getLabel('LBL_Field_Identifier', $adminLangId),
    'vflds_type' => Labels::getLabel('LBL_Field_Type', $adminLangId),
    'vflds_required' => Labels::getLabel('LBL_IS_REQUIRED', $adminLangId),
    'vflds_active' => Labels::getLabel('LBL_Active', $adminLangId),
    'action' => Labels::getLabel('LBL_Action', $adminLangId),
);
if (!$canEdit) {
    unset($arr_flds['select_all']);
}
$tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table table--hovered table-responsive'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $key => $val) {
    if ('select_all' == $key) {
        $th->appendElement('th')->appendElement('plaintext', array(), '<label class="checkbox"><input title="' . $val . '" type="checkbox" onclick="selectAll( $(this) )" class="selectAll-js"></label>', true);
    } else {
        $e = $th->appendElement('th', array(), $val);
    }
}

$sr_no = 0;
foreach ($listing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr', array());
    $tr->setAttribute("id", $row['vflds_id']);

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'vflds_identifier':
                if (empty($row['vflds_name'])) {
                    $td->appendElement('plaintext', array(), $row['vflds_name'], true);
                    $td->appendElement('br', array());
                    $td->appendElement('plaintext', array(), '(' . $row[$key] . ')', true);
                } else {
                    $td->appendElement('plaintext', array(), $row[$key], true);
                }
                break;
            case 'vflds_type':
                $fldsTypeArr = VerificationFields::getFldTypeArr($adminLangId);
                $str = isset($row[$key]) ? $fldsTypeArr[$row[$key]] : '';
                $td->appendElement('plaintext', array(), $str, true);
                break;
            case 'vflds_required':
                $yesNoArr = applicationConstants::getYesNoArr($adminLangId);
                $str = isset($row[$key]) ? $yesNoArr[$row[$key]] : '';
                $td->appendElement('plaintext', array(), $str, true);
                break;
            case 'vflds_active':
                /* $active = "active";
                if (!$row['vflds_active']) {
                    $active = '';
                }
                $statucAct = ($canEdit === true) ? 'toggleStatus(this)' : '';
                $str = '<label id="' . $row['vflds_id'] . '" class="statustab ' . $active . '" onclick="' . $statucAct . '">
                      <span data-off="' . Labels::getLabel('LBL_Active', $adminLangId) . '" data-on="' . Labels::getLabel('LBL_Inactive', $adminLangId) . '" class="switch-labels"></span>
                      <span class="switch-handles"></span>
                    </label>';
                $td->appendElement('plaintext', array(), $str, true); */
                $active = "";
                if ($row['vflds_active']) {
                    $active = 'checked';
                }

                $statusAct = ($canEdit === true) ? 'toggleStatus(event,this,' . applicationConstants::YES . ')' : 'toggleStatus(event,this,' . applicationConstants::NO . ')';
                $statusClass = ($canEdit === false) ? 'disabled' : '';
                $str = '<label class="statustab -txt-uppercase">
                     <input ' . $active . ' type="checkbox" id="switch' . $row['vflds_id'] . '" value="' . $row['vflds_id'] . '" onclick="' . $statusAct . '" class="switch-labels"/>
                                      	<i class="switch-handles ' . $statusClass . '"></i></label>';
                $td->appendElement('plaintext', array(), $str, true);

                break;
            case 'action':
                $ul = $td->appendElement("ul", array("class" => "actions actions--centered"));
                if ($canEdit) {
                    $li = $ul->appendElement("li", array('class' => 'droplink'));
                    $li->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'button small green', 'title' => Labels::getLabel('LBL_MANAGE_VERIFICATION_FIELDS', $adminLangId)), '<i class="ion-android-more-horizontal icon"></i>', true);
                    $innerDiv = $li->appendElement('div', array('class' => 'dropwrap'));
                    $innerUl = $innerDiv->appendElement('ul', array('class' => 'linksvertical'));

                    $innerLi = $innerUl->appendElement('li');
                    $innerLi->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'button small green', 'title' => Labels::getLabel('LBL_Edit', $adminLangId), "onclick" => "addFieldForm(" . $row['vflds_id'] . ")"), Labels::getLabel('LBL_Edit', $adminLangId), true);
                }
                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
}

if (count($listing) == 0) {
    $tbl->appendElement('tr')->appendElement('td', array('colspan' => count($arr_flds)), Labels::getLabel('LBL_No_Records_Found', $adminLangId));
}
echo $tbl->getHtml();
