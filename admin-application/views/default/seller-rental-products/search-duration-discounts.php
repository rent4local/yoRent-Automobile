<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="scroll scroll-x js-scrollable table-wrap">
    <?php
    $arr_flds = array(
        /* 'select_all' => '', */
        'product_name' => Labels::getLabel('LBL_Name', $adminLangId),
        'produr_rental_duration' => Labels::getLabel('LBL_Minimum_Duration', $adminLangId),
        'produr_discount_percent' => Labels::getLabel('LBL_Discount', $adminLangId),
    );
    if ($canEdit) {
        $arr_flds['action'] = '';
    }
    if (!$canEdit || 1 > count($arrListing)) {
        unset($arr_flds['select_all']);
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
        $splPriceId = $row['produr_id'];
        $selProdId = $row['selprod_id'];
        $editListingFrm = new Form('editListingFrm-' . $splPriceId, array('id' => 'editListingFrm-' . $splPriceId));
        foreach ($arr_flds as $column => $lblTitle) {
            $tr->setAttribute('id', 'row-' . $splPriceId);
            $td = $tr->appendElement('td');
            switch ($column) {
                case 'select_all':
                    $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="selprod_ids[' . $splPriceId . ']" value=' . $selProdId . '></label>', true);
                    break;
                case 'product_name':
                    // last Param of getProductDisplayTitle function used to get title in html form.
                    $txt = '<div class="item__description">';
                    $productName = SellerProduct::getProductDisplayTitle($selProdId, $adminLangId, true). ' <br /> '. Labels::getLabel('LBL_Seller:', $adminLangId) . ' ' . $row['seller_name'];
                    $txt .= '<div class="item__title">' . $productName . '</div>';
                    $txt .= '</div>';
                    $td->appendElement('plaintext', array(), $txt, true);
                    break;
                case 'produr_rental_duration' :
                    $durationType = (isset($durationTypes[$row['sprodata_duration_type']])) ? $durationTypes[$row['sprodata_duration_type']] : '';
                    $minimumDuration = $row['produr_rental_duration'] . ' ' . $durationType;
                    $td->appendElement('plaintext', array(), $minimumDuration, true);
                    break;
                case 'action':
                    $td->appendElement(
                            'a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon',
                        'title' => Labels::getLabel('LBL_Edit', $adminLangId), "onclick" => "sellerProductDurationDiscountForm(" . $selProdId . ", " . $row['produr_id'] . ")"), "<i class='fa fa-edit  icon'></i>", true
                    );
                    $td->appendElement(
                            'a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon',
                        'title' => Labels::getLabel('LBL_Delete', $adminLangId), "onclick" => "deleteSellerProductDurationDiscount(" . $row['produr_id'] . ")"), "<i class='fa fa-trash  icon'></i>", true
                    );
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
    $message = Labels::getLabel('LBL_No_Records_Found', $adminLangId);
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $adminLangId, 'message' => $message));
}
?>
<?php
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmSearchSpecialPricePaging'));

$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'callBackJsFunc' => 'goToSearchPage', 'adminLangId' => $adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
