<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="page">
    <div class="container container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row justify-content-between">
                        <div class="col--first col-lg-6">
                            <span class="page__icon"><i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Manage_Seller_Product', $adminLangId); ?>  </h5>
                            <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <div class="tabs_nav_container wizard-tabs-horizontal">  
                    <?php if (ALLOW_SALE) { ?>
                    <ul class="tabs_nav">
                        <li><a class="<?php echo ($activeTab == applicationConstants::PRODUCT_FOR_RENT) ? "active" : ""; ?> tabs_001" rel="tabs_001" href="javascript:void(0)">
                                <div class="tabs-head">
                                    <div class="tabs-title"><?php echo Labels::getLabel('LBL_Rent', $adminLangId); ?><span><?php echo Labels::getLabel('LBL_Add/Update_Rental_Details', $adminLangId); ?></span></div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a rel="tabs_002" class="tabs_002 <?php echo ($activeTab == applicationConstants::PRODUCT_FOR_SALE) ? "active" : ""; ?>" href="javascript:void(0)"> 
                                <div class="tabs-head">
                                    <div class="tabs-title"><?php echo Labels::getLabel('LBL_Sale', $adminLangId); ?><span><?php echo Labels::getLabel('LBL_Add/Update_Sale_Details', $adminLangId); ?></span></div>
                                </div>
                            </a>
                        </li>
                    </ul>
                    <?php } ?>
                    <div class="tabs_panel_wrap">
                        <div id="tabs_001" class="tabs_panel" style="display: <?php echo ($activeTab == applicationConstants::PRODUCT_FOR_RENT) ? "block" : "none"; ?>;"></div>
                        <div id="tabs_002" class="tabs_panel" style="display: <?php echo ($activeTab == applicationConstants::PRODUCT_FOR_SALE) ? "block" : "none"; ?>;"> </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
var SELPROD_TYPE = '<?php echo $activeTab; ?>';
var productId = '<?php echo $productId;?>';
var selprodId = '<?php echo $selprodId;?>';

<?php if ($activeTab == applicationConstants::PRODUCT_FOR_SALE) {  ?>
    addSellerProductSaleForm(<?php echo $productId;?>, <?php echo $selprodId;?>);
<?php } else {  ?> 
addSellerProductForm(<?php echo $productId;?>, <?php echo $selprodId;?>);
<?php } ?>
</script>


