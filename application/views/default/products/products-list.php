<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
?>
<div id="productsList">
    <?php
    if (isset($postedData['vtype']) && $postedData['vtype'] == 'map') {
        include(CONF_THEME_PATH_WITH_THEME_NAME . 'products/products-list-map.php');
    } else {
        $colMdVal = isset($colMdVal) ? $colMdVal : 4;
        $displayProductNotAvailableLable = false;
        if (trim(FatApp::getConfig('CONF_GOOGLEMAP_API_KEY', FatUtility::VAR_STRING, '')) != '') {
            $displayProductNotAvailableLable = true;
        }
        ?>

        <?php if ($products) { ?>     
            <div class="product-listing" data-view="<?php echo $colMdVal; ?>">
                <?php
                $showActionBtns = !empty($showActionBtns) ? $showActionBtns : false;
                $isWishList = isset($isWishList) ? $isWishList : 0;
                ?>
                <?php
                foreach ($products as $product) {
                    $productUrl = UrlHelper::generateUrl('Products', 'View', array($product['selprod_id']));
                    $extraClsss = '';
                    //include('../_partial/collection/product-listing-tile-layout.php');

                    $this->includeTemplate('_partial/collection/product-listing-tile-layout.php', array('product' => $product, 'siteLangId' => $siteLangId, 'extraClsss' => '', 'compProdCount' => $compProdCount,
                        'comparedProdSpecCatId' => $comparedProdSpecCatId));
                    ?>
                <?php } ?>
            </div>

            <?php
            $searchFunction = 'goToProductListingSearchPage';
            if (isset($pagingFunc)) {
                $searchFunction = $pagingFunc;
            }

            $postedData['page'] = (isset($page)) ? $page : 1;
            $postedData['recordDisplayCount'] = $recordCount;
            echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmProductSearchPaging', 'id' => 'frmProductSearchPaging'));
            $pagingArr = array('pageCount' => $pageCount, 'page' => $postedData['page'], 'recordCount' => $recordCount, 'callBackJsFunc' => $searchFunction, 'siteLangId' => $siteLangId);
            $this->includeTemplate('_partial/pagination.php', $pagingArr, false);
            ?>
            <?php
        } else {
            $arr['recordDisplayCount'] = $recordCount;
            echo FatUtility::createHiddenFormFromData($arr, array('name' => 'frmProductSearchPaging', 'id' => 'frmProductSearchPaging'));
            $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
            $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId, 'message' => $message));
            ?>
            <?php
        }
    }
    ?>
</div>
