<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$colMdVal = isset($colMdVal) ? $colMdVal : 3;
$displayProductNotAvailableLable = false;
if (trim(FatApp::getConfig('CONF_GOOGLEMAP_API_KEY', FatUtility::VAR_STRING, '')) != '') {
    $displayProductNotAvailableLable = true;
}
$pageRecordCount = (isset($pageRecordCount)) ? $pageRecordCount : count($products);
$pageSize = (isset($pageSize)) ? $pageSize : $postedData['pageSize'];
?>
<div class="listing-products -listing-products">
    <div id="productsList" class="<?php echo (isset($postedData['vtype']) && $postedData['vtype'] == 'list') ? "listing-products--list" : "listing-products--grid";?>">
    <?php
        if (isset($postedData['vtype']) && $postedData['vtype'] == 'map') {
            include(CONF_THEME_PATH_WITH_THEME_NAME . 'products/products-list-map.php');
        } else {
            if ($products) { ?> 
                <div class="product-listing" data-view="<?php echo $colMdVal; ?>">
                    <?php
                    $showActionBtns = !empty($showActionBtns) ? $showActionBtns : false;
                    $isWishList = isset($isWishList) ? $isWishList : 0;
                    foreach ($products as $product) {
                        $product['isListingPage'] = true;
                        $productUrl = UrlHelper::generateUrl('Products', 'View', array($product['selprod_id']));
                        $extraClsss = '';
                        $this->includeTemplate('_partial/collection/product-layout-1-list.php', 
                        array(
                            'product' => $product, 
                            'siteLangId' => $siteLangId, 
                            'extraClsss' => '', 
                            'compProdCount' => $compProdCount,
                            'comparedProdSpecCatId' => $comparedProdSpecCatId, 
                            'searchProductType' => (isset($postedData['producttype']) && !empty($postedData['producttype'])) ? $postedData['producttype'][0] : applicationConstants::PRODUCT_FOR_RENT,
                        ));
                    } ?>
                </div>

                <?php
                $searchFunction = 'goToProductListingSearchPage';
                if (isset($pagingFunc)) {
                    $searchFunction = $pagingFunc;
                }
                $postedData['page'] = (isset($page)) ? $page : 1;
                $postedData['recordDisplayCount'] = $recordCount;
                echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmProductSearchPaging', 'id' => 'frmProductSearchPaging'));
                $pagingArr = array('pageCount' => $pageCount, 'page' => $postedData['page'], 'recordCount' => $recordCount, 'callBackJsFunc' => $searchFunction, 'removePageCentClass' => 1, 'siteLangId' => $siteLangId); 
                ?>
                <div class="collection-pager">
                    <?php
                    $this->includeTemplate('_partial/pagination.php', $pagingArr, false);
                    if (!isset($removePageSize)) {
                    ?>
                        <select name="pageSizeSelect" id="pageSizeSelect" class="custom-select sorting-select">
                            <?php foreach ($pageSizeArr as $key => $val) { ?>
                                <option value="<?php echo $key; ?>" <?php echo ($key == $pageSize) ? 'selected' : ''; ?>><?php echo $val; ?>
                                </option>
                            <?php } ?>
                        </select>
                    <?php } ?>
                </div>
            <?php
            } else {
                $arr['recordDisplayCount'] = $recordCount;
                $pageItemCount = $pageSize * ($page - 1) + $pageRecordCount;
                $postedData['recordDisplayCountString'] = sprintf(Labels::getLabel('LBL_Showing_%s_item(s)_from_%s_item(s)', $siteLangId), $pageItemCount, $recordCount);
                echo FatUtility::createHiddenFormFromData($arr, array('name' => 'frmProductSearchPaging', 'id' => 'frmProductSearchPaging'));
                $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
                $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId, 'message' => $message));
            }
        }
        ?>
    </div>
    <script>
        $(document).ready(function() {
            var e = document.getElementById("pageSizeSelect");
            if (e != undefined && e != null) {
                var pageSize = e.options[e.selectedIndex].value;
                $('#pageSize').val(pageSize);
            }
        })
    </script>