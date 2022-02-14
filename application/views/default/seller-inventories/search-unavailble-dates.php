<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="scroll scroll-x js-scrollable table-wrap">
    <?php
    $arr_flds = array(
        'product_name' => Labels::getLabel('LBL_Product_Name', $siteLangId),
        'pu_start_date' => Labels::getLabel('LBL_Start_date', $siteLangId),
        'pu_end_date' => Labels::getLabel('LBL_End_Date', $siteLangId),
        'pu_quantity' => Labels::getLabel('LBL_Unavailable_Quantity', $siteLangId),
        'action' => '',
    );
    
    if (!$canEdit) {
        unset($arr_flds['action']);
    }
    

    $tableClass = '';
    if (0 < count($arrListing)) {
        $tableClass = "table-justified";
    }
    $tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table splPriceList-js ' . $tableClass));
    $th = $tbl->appendElement('thead')->appendElement('tr', array('class' => ''));
    foreach ($arr_flds as $column => $lblTitle) {
        if ('select_all' == $column) {
            $th->appendElement('th')->appendElement('plaintext', array(), '<label class="checkbox"><input title="' . $lblTitle . '" type="checkbox" onclick="selectAll($(this))" class="selectAll-js"></label>', true);
        } else {
            $th->appendElement('th', array(), $lblTitle);
        }
    }

    foreach ($arrListing as $sn => $row) {
        $tr = $tbl->appendElement('tr', array());
        foreach ($arr_flds as $column => $lblTitle) {
            $tr->setAttribute('id', 'row-' . $row['pu_start_date']);
            $td = $tr->appendElement('td');
            switch ($column) {
                case 'select_all':
                    $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="selprod_ids[' . $splPriceId . ']" value=' . $selProdId . '></label>', true);
                    break;
                case 'product_name':
                    $txt = '<div class="item__description">';
                    $productName = SellerProduct::getProductDisplayTitle($row['selprod_id'], $siteLangId, true);
                    $txt .= '<div class="item__title">' . $productName . '</div>';
                    $txt .= '</div>';
                    $td->appendElement('plaintext', array(), $txt, true);
                    break;
                case 'pu_start_date':
                case 'pu_end_date':
                    $date = FatDate::format($row[$column]);
                    $td->appendElement('plaintext', array(), $date, true);
                    break;

                case 'action':
                    $ul = $td->appendElement("ul", array("class" => "actions"), '', true);
                    $li = $ul->appendElement("li");
                    $li->appendElement('a', array('href' => 'javascript:void(0)', 'class' => '', 'title' => Labels::getLabel('LBL_Edit', $siteLangId), "onclick" => "productRentalUnavailableDatesForm(" . $row['selprod_id'] . ", " . $row['pu_id'] . ")"), '<i class="fa fa-edit"></i>', true);
                    $li = $ul->appendElement("li");
                    $li->appendElement('a', array('href' => 'javascript:void(0)', 'class' => '', 'title' => Labels::getLabel('LBL_Delete', $siteLangId), "onclick" => "deleteRentalUnavailableDates(" . $row['selprod_id'] . ", " . $row['pu_id'] . ")"), '<i class="fa fa-trash"></i>', true);
                    break;
                default:
                    $td->appendElement('plaintext', array(), $row[$column], true);
                    break;
            }
        }
    }

    $frm = new Form('frmSplPriceListing', array('id' => 'frmSplPriceListing'));
    $frm->setFormTagAttribute('class', 'form');

    echo $frm->getFormTag();
    echo $tbl->getHtml();
    ?>
</form>
</div>
<?php
if (count($arrListing) == 0) {
    $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId, 'message' => $message));
}
?>
<?php
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmSearchSpecialPricePaging'));

$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'callBackJsFunc' => 'goToSearchPage', 'siteLangId' => $siteLangId, 'recordCount' => $recordCount, 'pageSize' => $pageSize, 'removePageCentClass' => true);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
