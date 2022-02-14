<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$layout = Language::getLayoutDirection($langId);
$isAutoComplete = (!empty(FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, ''))) ? 1 : 0;
?>
<div class="row" dir="<?php echo $layout; ?>">
    <div class="col-md-12">
        <div class="scroll scroll-x js-scrollable table-wrap">
            <?php
            if (count($productSpecifications) > 0) {
                $arr_flds = array(
                    'prodspec_name' => Labels::getLabel('LBL_Specification_Name', $siteLangId),
                    'prodspec_value' => Labels::getLabel('LBL_Specification_Value', $siteLangId),
                    'prodspec_group' => Labels::getLabel('LBL_Specification_Group', $siteLangId),
                    'action' => ''
                );

                $tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table table-justified'));
                $th = $tbl->appendElement('thead')->appendElement('tr');
                foreach ($arr_flds as $key => $val) {
                    if ($key == 'prodspec_name' || $key == 'prodspec_value' || $key == 'prodspec_group') {
                        $e = $th->appendElement('th', array('width' => '27%'), $val);
                    } else {
                        $e = $th->appendElement('th', array(), $val);
                    }
                }

                foreach ($productSpecifications as $specification) {
                    $tr = $tbl->appendElement('tr');
                    foreach ($arr_flds as $key => $val) {
                        $td = $tr->appendElement('td');
                        switch ($key) {
                            case 'action':
                                $prodSpecId = $specification['prodspec_id'];
                                $ul = $td->appendElement('ul', array('class' => 'actions'));
                                $li = $ul->appendElement('li');
                                $li->appendElement('a', array('href' => 'javascript:void(0)', 'title' => Labels::getLabel('LBL_Edit', $siteLangId), 'onClick' => 'prodSpecificationSection(' . $langId . ',' . $prodSpecId . ')'), '<i class="fa fa-edit"></i>', true);
                                if ($siteDefaultLang == $langId || $isAutoComplete == 0) {
                                    $lia = $li->appendElement('li');
                                    $lia->appendElement('a', array('href' => 'javascript:void(0)', 'title' => Labels::getLabel('LBL_Delete', $siteLangId), 'onClick' => 'deleteProdSpec(' . $prodSpecId . ',' . $langId . ')'), '<i class="fa fa-trash"></i>', true);
                                }
                                break;
                            default:
                                $td->appendElement('plaintext', array(), $specification[$key], true);
                                break;
                        }
                    }
                }
                echo $tbl->getHtml();
            }
            if (count($productSpecifications) == 0 && $siteDefaultLang != $langId) {
                $message = Labels::getLabel('LBL_No_Specifications_addded_yet', $siteLangId);
                $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId, 'message' => $message));
            }
            ?>
        </div>
    </div>
</div>
<?php ?>

