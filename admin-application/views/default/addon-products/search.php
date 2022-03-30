<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    'selprod_title' => Labels::getLabel('LBL_Addons_Name', $adminLangId),
    'attached_products' => Labels::getLabel('LBL_Attached_Products', $adminLangId),
    'shop_name' => Labels::getLabel('LBL_Shop_Name', $adminLangId)
);

$tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table'));
$thead = $tbl->appendElement('thead');
$th = $thead->appendElement('tr', array('class' => ''));

foreach ($arr_flds as $key => $val) {
    if ('selprod_title' == $key) {
        $th->appendElement('th', array('width' => '20%'), $val);
    } else {
        $th->appendElement('th', array('width' => '60%'), $val);
    }
}

foreach ($arrListing as $selProdId => $product) {
    $tr = $tbl->appendElement('tr', array());
    foreach ($arr_flds as $key => $val) {
        $tr->setAttribute('id', 'row-' . $selProdId);
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'selprod_title':
                $productName = "<span class='js-prod-name'>" . $product['selprod_title'] . "</span>";
                $td->appendElement('plaintext', array(), $productName, true);
                break;
            case 'attached_products':
                $div = $td->appendElement('div', array("class" => "list-tag-wrapper", "data-scroll-height" => "150"));
                $ul = $div->appendElement("ul", array("class" => "list-tags"));
                $attachedProducts = (!empty($attachedProductsData) && isset($attachedProductsData[$selProdId])) ? $attachedProductsData[$selProdId] : [];
                if (!empty($attachedProducts)) {
                    foreach ($attachedProducts as $attProd) {
                        $li = $ul->appendElement("li");
                        $li->appendElement('plaintext', array(), '<span>' . $attProd['selprod_title'] . '</span>', true);
                    }
                }
                break;
            case 'shop_name':
                $shopName = "<a href='javascript:void(0)' onclick='redirectfunc(\"" . UrlHelper::generateUrl('Shops') . "\", " . $product['shop_id'] . ")'>" . $product['shop_name'] . "</a>";
                $userName = "<a href='javascript:void(0)' onclick='redirectfunc(\"" . UrlHelper::generateUrl('Users') . "\", " . $product['shop_user_id'] . ")'>" . $product['user_name'] . "</a>";
                $td->appendElement('plaintext', array(), $shopName . ' (' . $userName . ')', true);
                break;
            default:
                $td->appendElement('plaintext', array(), $product[$key], true);
                break;
        }
    }
}

echo $tbl->getHtml();
if (count($arrListing) == 0) {
    $message = Labels::getLabel('LBL_No_Records_Found', $adminLangId);
    $this->includeTemplate('_partial/no-record-found.php', array('adminLangId' => $adminLangId, 'message' => $message));
}
?>
</form>
<?php
$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmProductSearchPaging'));

$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'callBackJsFunc' => 'goToSearchPage', 'adminLangId' => $adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
