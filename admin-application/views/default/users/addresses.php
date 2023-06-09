<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<section class="section">
    <h1><?php echo Labels::getLabel('LBL_User_Addresses', $adminLangId); ?></h1>
    <div class="tabs_nav_container responsive flat">
        <ul class="tabs_nav">
            <li><a href="javascript:void(0)" onclick="userForm(<?php echo $user_id ?>);"><?php echo Labels::getLabel('LBL_General', $adminLangId); ?></a></li>
            <li><a href="javascript:void(0)" onclick="addBankInfoForm(<?php echo $user_id ?>);"><?php echo Labels::getLabel('LBL_Bank_Info', $adminLangId); ?></a></li>
            <li><a class="active" href="javascript:void(0)" onclick="userAddresses(<?php echo $user_id ?>);"><?php echo Labels::getLabel('LBL_Addresses', $adminLangId); ?></a></li>
        </ul>
        <div class="tabs_panel_wrap">
            <a href="javascript:void(0)" class="themebtn btn-default btn-sm" onClick="addOneAddress(<?php echo $user_id ?>,0)"><?php echo Labels::getLabel('LBL_Add_New', $adminLangId); ?></a>
            <div class="tabs_panel">
                <?php
                $arr_flds = array(
                    'listserial' => Labels::getLabel('LBL_#', $adminLangId),
                    'addr_title' => Labels::getLabel('LBL_Identifier', $adminLangId),
                    'user_address' => Labels::getLabel('LBL_Address', $adminLangId),
                    'addr_is_default' => Labels::getLabel('LBL_Default', $adminLangId),
                    'action' => Labels::getLabel('LBL_Action', $adminLangId),
                );
                $tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table table-responsive'));
                $th = $tbl->appendElement('thead')->appendElement('tr');
                foreach ($arr_flds as $key => $val) {
                    $e = $th->appendElement('th', array(), $val, true);
                }
                $sr_no = 0;
                foreach ($addresses as $sn => $row) {
                    $sr_no++;
                    $tr = $tbl->appendElement('tr');

                    foreach ($arr_flds as $key => $val) {
                        $td = $tr->appendElement('td');
                        switch ($key) {
                            case 'listserial':
                                $td->appendElement('plaintext', array(), $sr_no);
                                break;
                            case 'user_address':
                                $address = $row['addr_name'] . '<br>';
                                $address .= $row['addr_address1'];
                                $address .= (!empty($row['add_address2'])) ? ',' . $row['addr_address2'] . '<br>' : '<br>';
                                $address .= (!empty($row['addr_city'])) ? $row['addr_city'] . ',' : '';
                                $address .= (!empty($row['state_name'])) ? $row['state_name'] . '<br>' : '';
                                $address .= (!empty($row['country_name'])) ? $row['country_name'] . '<br>' : '';
                                $address .= (!empty($row['addr_zip'])) ? Labels::getLabel('LBL_Postal_Code', $adminLangId) . ': ' . $row['addr_zip'] . '<br>' : '';
                                $address .= (!empty($row['addr_phone'])) ? Labels::getLabel('LBL_Phone', $adminLangId) . ': ' . $row['addr_dial_code'] . ' ' . $row['addr_phone'] . '<br>' : '';

                                $td->appendElement('plaintext', array(), $address, true);
                                break;
                            case 'addr_is_default':
                                $str = ($row['addr_is_default'] == 1) ? Labels::getLabel('LBL_Yes', $adminLangId) : Labels::getLabel('LBL_No', $adminLangId);
                                $td->appendElement('plaintext', array(), $str, true);
                                break;
                            case 'action':
                                $ul = $td->appendElement("ul", array("class" => "actions actions--centered"));
                                if ($canEdit) {
                                    $li = $ul->appendElement("li", array('class' => 'droplink'));
                                    $li->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'button small green', 'title' => Labels::getLabel('LBL_Edit', $adminLangId)), '<i class="ion-android-more-horizontal icon"></i>', true);
                                    $innerDiv = $li->appendElement('div', array('class' => 'dropwrap'));
                                    $innerUl = $innerDiv->appendElement('ul', array('class' => 'linksvertical'));
                                    $innerLi = $innerUl->appendElement('li');
                                    $innerLi->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'button small green', 'title' => Labels::getLabel('LBL_Edit', $adminLangId), "onclick" => "addOneAddress(" . $row['addr_record_id'] . "," . $row['addr_id'] . ")"), Labels::getLabel('LBL_Edit', $adminLangId), true);
                                    $innerLi = $innerUl->appendElement('li');
                                    $innerLi->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'button small green', 'title' => Labels::getLabel('LBL_Delete', $adminLangId), "onclick" => "deleteAddress(" . $row['addr_record_id'] . "," . $row['addr_id'] . ")"), Labels::getLabel('LBL_Delete', $adminLangId), true);
                                }
                                break;
                            default:
                                $td->appendElement('plaintext', array(), $row[$key], true);
                                break;
                        }
                    }
                }
                if (count($addresses) == 0) {
                    $tbl->appendElement('tr')->appendElement('td', array('colspan' => count($arr_flds)), Labels::getLabel('LBL_No_Records_Found', $adminLangId));
                }
                echo $tbl->getHtml();
                ?>
            </div>
        </div>
    </div>
</section>