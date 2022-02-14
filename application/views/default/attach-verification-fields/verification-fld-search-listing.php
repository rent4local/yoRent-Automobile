<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
?>
<div class="scroll scroll-x js-scrollable table-wrap">
    <?php
    $arr_flds = array(
        'vflds_name' => Labels::getLabel('LBL_Field_Name', $siteLangId),
        'vflds_type' => Labels::getLabel('LBL_Type', $siteLangId),
        'vflds_required' => Labels::getLabel('LBL_Is_Required', $siteLangId),
    );
    $tableClass = '';
    /* if (0 < count($vFlds)) {
        $tableClass = "table-justified";
    } */
    $tbl = new HtmlElement('table', array('class' => 'table ' . $tableClass));
    $th = $tbl->appendElement('thead')->appendElement('tr', array('class' => ''));
    foreach ($arr_flds as $val) {
        $e = $th->appendElement('th', array(), $val);
    }
    $sr_no = 0;
    
    foreach ($vFlds as $sn => $flds) {
        $sr_no++;
        $tr = $tbl->appendElement('tr', array('class' => ''));

        foreach ($arr_flds as $key => $val) {
            $td = $tr->appendElement('td');
            switch ($key) {
                case 'vflds_type':
                    $txt = '';
                    $fldTypeArr = VerificationFields::getFldTypeArr($siteLangId);
                    $txt = $fldTypeArr[$flds[$key]];
                    $td->appendElement('plaintext', array(), $txt, true);
                    break;
                case 'vflds_required':
                    $txt = '';
                    $fldTypeArr = applicationConstants::getYesNoArr($siteLangId);
                    $txt = $fldTypeArr[$flds[$key]];
                    $td->appendElement('plaintext', array(), $txt, true);
                    break;
                
                default:
                    $td->appendElement('plaintext', array(), '' . $flds[$key], true);
                    break;
            }
        }
    }
    echo $tbl->getHtml();
    if (count($vFlds) == 0) {
        $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
        $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId, 'message' => $message));
    }
    ?>
</div>
<?php
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmSrchPaging'));
$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'callBackJsFunc' => 'goToSearchPage', 'siteLangId' => $siteLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
