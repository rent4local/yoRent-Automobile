<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$reloadAction = 'reloadListProduct';
$callbackAction = 'goToSearchPage';
$imageAction = 'product';
if ($type == SellerProduct::PRODUCT_TYPE_ADDON) {
    $reloadAction  = 'reloadListServices';
    $callbackAction = 'goToServiceSearchPage';
    $imageAction = 'addonProduct';
}

if (!empty($productsData)) { ?>
    <ul class="product-list">
        <?php foreach ($productsData as $sn => $row) { ?>
        <li>
            <div class="item">
                <figure class="item__pic">
                    <a href="javascript:void(0)"><img class="" src="<?php echo  UrlHelper::generateUrl("Image", $imageAction, array($row['product_id'], "SMALL" , 0, 0, 1)); ?>" alt="" width="50"></a>
                </figure>
                <div class="item__description">
                    <div class="item__title">
                        <?php echo $row['product_name']?>
                    </div>
                </div>
            </div>
            <?php if ($canEdit && isset($profileData['lcp_is_default']) && $profileData['lcp_is_default'] != 1) { ?>
                <a class="close-layer close-layer--sm" href="javascript:void(0)" onclick="removeProductFromProfile(<?php echo $row['product_id'] .', '. $type; ?>)" title="<?php echo Labels::getLabel('LBL_Remove_Product_from_profile', $siteLangId);?>"></a>
            <?php }?>
        </li>
        <?php }?>
    </ul>
 
<?php }?>
<?php 

$postedData['type'] = $type;

if (empty($productsData)) {
    //echo $tbl->getHtml();
    $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId, 'message' => $message));
} else {
    $frm = new Form('frmProductListing', array('id' => 'frmProductListing'));
    $frm->setFormTagAttribute('class', 'web_form last_td_nowrap');
    $frm->setFormTagAttribute('onsubmit', 'formAction(this, '. $reloadAction .'); return(false);');
    echo $frm->getFormTag();
    ?>
    </form>
</div>
<?php $postedData['page'] = $page;
	echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmProductSearchPaging_'. $type));
	$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'siteLangId' => $siteLangId, 'callBackJsFunc' => $callbackAction, 'siteLangId' => $siteLangId);
	$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
} 
?>