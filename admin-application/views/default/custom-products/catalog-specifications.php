<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$layout = Language::getLayoutDirection($langId);
$isAutoComplete = (!empty(FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, ''))) ? 1 : 0;
if (count($productSpecifications) > 0) { ?>
    <div class="row" dir="<?php echo $layout; ?>">
        <div class="col-md-12">
            <div class="tablewrap js-scrollable table-wrap">
                <?php
                $arr_flds = array(
                    'prod_spec_name' => Labels::getLabel('LBL_Specification_Name', $adminLangId),
                    'prod_spec_value' => Labels::getLabel('LBL_Specification_Value', $adminLangId),
                    'prod_spec_group' => Labels::getLabel('LBL_Specification_Group', $adminLangId),
                    'action' => ''
                );

                $tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table table-bordered'));
                $th = $tbl->appendElement('thead')->appendElement('tr');
                foreach ($arr_flds as $key => $val) {
                    if ($key == 'prodspec_name' || $key == 'prod_spec_value' || $key == 'prod_spec_group') {
                        $e = $th->appendElement('th', array('width' => '27%'), $val);
                    } else {
                        $e = $th->appendElement('th', array(), $val);
                    }
                }

                foreach ($productSpecifications as $keyData => $specification) {
                    $tr = $tbl->appendElement('tr');
                    foreach ($arr_flds as $key => $val) {
                        $td = $tr->appendElement('td');
                        switch ($key) {
                            case 'action':
                                $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-sm btn-clean btn-icon btn-icon-md', 'title' => Labels::getLabel('LBL_Edit', $adminLangId), 'onClick' => 'prodSpecificationSection(' . $langId . ',' . $keyData . ')'), '<i class="fa fa-edit"></i>', true);
                                if ($siteDefaultLang == $langId || $isAutoComplete == 0) {
                                    $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-sm btn-clean btn-icon btn-icon-md', 'title' => Labels::getLabel('LBL_Delete', $adminLangId), 'onClick' => 'deleteProdSpec(' . $keyData . ',' . $langId . ')'), '<i class="fa fa-trash"></i>', true);
                                }
                                break;
                            default:
                                $td->appendElement('plaintext', array(), $specification[$key], true);
                                break;
                        }
                    }
                }
                echo $tbl->getHtml();
                ?>
            </div>
        </div>
    </div>
    <?php
} else {
    if ($siteDefaultLang != $langId) {
        $message = Labels::getLabel('LBL_No_Specifications_addded_yet', $adminLangId);
        $this->includeTemplate('_partial/no-record-found.php', array('adminLangId' => $adminLangId, 'message' => $message));
    }
}
?>

