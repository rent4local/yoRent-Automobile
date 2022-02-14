<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="sectionhead">
    <h4><?php echo Labels::getLabel('LBL_Navigations', $adminLangId); ?> </h4>
    <?php
    if ($canEdit) {
        $data = [
            'adminLangId' => $adminLangId,
            'deleteButton' => false
        ];

        $this->includeTemplate('_partial/action-buttons.php', $data, false);
    }
    ?>
</div>
<div class="sectionbody">
    <div class="tablewrap">
        <?php 
        $arr_flds = array(
            'select_all' => Labels::getLabel('LBL_Select_all', $adminLangId),
            'listserial' => Labels::getLabel('LBL_#', $adminLangId),
            'nav_identifier' => Labels::getLabel('LBL_Title', $adminLangId),
            'nav_active'    =>    Labels::getLabel('LBL_Status', $adminLangId),
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

        $sr_no = 0;
        foreach ($arr_listing as $sn => $row) {
            $sr_no++;
            $tr = $tbl->appendElement('tr', array());
            foreach ($arr_flds as $key => $val) {
                $td = $tr->appendElement('td');
                switch ($key) {
                    case 'select_all':
                        $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="nav_ids[]" value=' . $row['nav_id'] . '></label>', true);
                        break;
                    case 'listserial':
                        $td->appendElement('plaintext', array(), $sr_no);
                        break;
                    case 'nav_identifier':
                        if ($row['nav_name'] != '') {
                            $td->appendElement('plaintext', array(), $row['nav_name'], true);
                            $td->appendElement('br', array());
                            $td->appendElement('plaintext', array(), '(' . $row[$key] . ')', true);
                        } else {
                            $td->appendElement('plaintext', array(), $row[$key], true);
                        }
                        break;
                    case 'nav_active':
                        $active = "";
                        if ($row['nav_active']) {
                            $active = 'checked';
                        }
                        $statusAct = ($canEdit === true) ? 'toggleStatus(event,this,' . applicationConstants::YES . ')' : 'toggleStatus(event,this,' . applicationConstants::NO . ')';
                        $statusClass = ($canEdit === false) ? 'disabled' : '';
                        $str = '<label class="statustab -txt-uppercase">
                         <input ' . $active . ' type="checkbox" id="switch' . $row['nav_id'] . '" value="' . $row['nav_id'] . '" onclick="' . $statusAct . '" class="switch-labels"/>
                        <i class="switch-handles ' . $statusClass . '"></i> </label>';
                        $td->appendElement('plaintext', array(), $str, true);
                        break;
                    case 'action':
                        if ($canEdit) {
                            $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Edit', $adminLangId), "onclick" => "addFormNew(" . $row['nav_id'] . ")"), "<i class='far fa-edit icon'></i>", true);
                        }
                        $td->appendElement('a', array('href' => "javascript:void(0)", 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Pages', $adminLangId), "onclick" => "pages(" . $row['nav_id'] . ")"), "<i class='ion-ios-paper icon'></i>", true);
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
        $frm = new Form('frmNavListing', array('id' => 'frmNavListing'));
        $frm->setFormTagAttribute('class', 'web_form last_td_nowrap actionButtons-js');
        $frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');
        $frm->setFormTagAttribute('action', UrlHelper::generateUrl('Navigations', 'toggleBulkStatuses'));
        $frm->addHiddenField('', 'status');

        echo $frm->getFormTag();
        echo $frm->getFieldHtml('status');
        echo $tbl->getHtml(); ?>
        </form>
    </div>
</div>