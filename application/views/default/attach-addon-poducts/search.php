<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    'selprod_title' => Labels::getLabel('LBL_Addons', $siteLangId),
    'attached_products' => Labels::getLabel('LBL_Attached_Products', $siteLangId)
);

$tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table volDiscountList-js'));
$thead = $tbl->appendElement('thead');
$th = $thead->appendElement('tr', array('class' => ''));

foreach ($arr_flds as $key => $val) {
    if ('selprod_title' == $key) {
        $th->appendElement('th', array('width' => '25%'), $val);
    } else {
        $th->appendElement('th', array('width' => '75%'), $val);
    }
}
foreach ($arrListing as $selProdId => $poduct) {
    $tr = $tbl->appendElement('tr', array());
    foreach ($arr_flds as $key => $val) {
        $tr->setAttribute('id', 'row-' . $selProdId);
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'select_all':
                $td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="selprod_ids[' . $selProdId . ']" value=' . $selProdId . '></label>', true);
                break;
            case 'selprod_title':
                $productName = "<span class='js-prod-name'>" . $poduct['selprod_title'] . "</span>";
                $td->appendElement('plaintext', array(), $productName, true);
                break;
            case 'attached_products':
                $div = $td->appendElement('div', array("class" => "list-tag-wrapper", "data-scroll-height" => "150"));
                $ul = $div->appendElement("ul", array("class" => "list-tags"));
                $attachedProducts = (!empty($attachedProductsData) && isset($attachedProductsData[$selProdId])) ? $attachedProductsData[$selProdId] : [];
                if (!empty($attachedProducts)) {
                    foreach ($attachedProducts as $attProd) {
                        $li = $ul->appendElement("li");
                        $removeIcon = '';
                        if ($canEdit) { 
                            $removeIcon = '<i class="remove_buyTogether remove_param fa fa-times" onClick="deleteAttachedProduct(' . $selProdId . ', ' . $attProd['selprod_id'] . ')"></i>';
                        }
                        $li->appendElement('plaintext', array(), '<span>' . $attProd['selprod_title'] . ' ' . $removeIcon . '</span>', true);
                    }
                }
                break;
            default:
                break;
        }
    }
}

echo $tbl->getHtml();
if (count($arrListing) == 0) {
    $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId, 'message' => $message));
}
$frm = new Form('frmVolDiscountListing', array('id' => 'frmVolDiscountListing'));
$frm->setFormTagAttribute('class', 'form');

echo $frm->getFormTag();
?>
</form>
<?php
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmProductSearchPaging'));

$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'callBackJsFunc' => 'goToSearchPage', 'siteLangId' => $siteLangId, 'pageSize' => $pageSize, 'removePageCentClass' => true);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
