<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php $this->includeTemplate('_partial/dashboardNavigation.php'); ?>
<main id="main-area" class="main"   >
    <div class="content-wrapper content-space">
        <div class="content-header row">
            <div class="col">
                <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Inventory_Setup', $siteLangId); ?></h2>
            </div>
            <div class="col-auto">
                <div class="btn-group">
                    <?php if ($isActiveSale) { ?>
                        <a href="<?php echo UrlHelper::generateUrl('sellerInventories', 'sales'); ?>" class="btn btn-outline-brand btn-sm">
                            <?php echo Labels::getLabel('LBL_Back_To_My_Inventory', $siteLangId) ?>
                        </a>
                    <?php } else { ?>
                        <a href="<?php echo UrlHelper::generateUrl('sellerInventories', 'products'); ?>" class="btn btn-outline-brand btn-sm">
                            <?php echo Labels::getLabel('LBL_Back_To_My_Inventory', $siteLangId) ?>
                        </a>
                    <?php } ?>
                </div>
            </div>
        </div>
        <div class="content-body">
            <div class="tabs">
                <ul class="tabs_nav-js">
                    <?php /* if ($isActiveSale == 0) { */ ?>
                    <?php if (!FatApp::getConfig('CONF_ALLOW_MEMBERSHIP_MODULE', FatUtility::VAR_INT, 0)) { ?>
                        <li>
                            <a <?php echo ($selprod_id > 0) ? 'class="tabs_001"' : ''; ?>  rel="tabs_001" href="javascript:void(0)">
                                <?php echo Labels::getLabel('LBL_Rent', $siteLangId); ?> <!-- <i class="tabs-icon fa fa-info-circle"  data-toggle="tooltip" data-placement="right" title="<?php echo Labels::getLabel('LBL_Setup_Rental_Details', $siteLangId); ?>">
                                </i> -->
                            </a>
                        </li>
                    <?php } else { ?>
                        <li>
                            <a <?php echo ($selprod_id > 0) ? 'class="tabs_002"' : ''; ?>  rel="tabs_002" href="javascript:void(0)">
                                <?php echo Labels::getLabel('LBL_Membership', $siteLangId); ?> <!-- <i class="tabs-icon fa fa-info-circle"  data-toggle="tooltip" data-placement="right" title="<?php echo Labels::getLabel('LBL_Setup_Membership_Details', $siteLangId); ?>">
                                </i> -->
                            </a>
                        </li>
                    <?php } ?>
                    <?php /* } */ ?>
                    <?php if (ALLOW_SALE) { ?>
                    <li>
                        <a <?php echo ($selprod_id > 0 || $isActiveSale == 1) ? 'class="tabs_003"' : ''; ?>  rel="tabs_003" href="javascript:void(0)">
                            <?php echo Labels::getLabel('LBL_Sale', $siteLangId); ?> <!-- <i class="tabs-icon fa fa-info-circle"  data-toggle="tooltip" data-placement="right" title="<?php echo Labels::getLabel('LBL_Setup_Sale_Details', $siteLangId); ?>">
                            </i> -->
                        </a>
                    </li>
                    <?php } ?>
                </ul>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="tabs__content">
                        <div id="tabs_001" class="tabs_panel" style="display: <?php echo ($isActiveSale == 0) ? "block;" : "none;";?>"></div>
                        <div id="tabs_002" class="tabs_panel" style="display: none;"></div>
                        <?php if (ALLOW_SALE) { ?>
                            <div id="tabs_003" class="tabs_panel" style="display: <?php echo ($isActiveSale == 0) ? "none;" : "block;";?>;"></div>
                        <?php } ?>    
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
    var product_id = <?php echo $product_id; ?>;
    var selprod_id = <?php echo $selprod_id; ?>;
    var ALLOW_SALE = <?php echo ALLOW_SALE; ?>;
    $(document).ready(function () {
    <?php if ($isActiveSale == 0) { ?>
    <?php if (!FatApp::getConfig('CONF_ALLOW_MEMBERSHIP_MODULE', FatUtility::VAR_INT, 0)) { ?>
                sellerProductForm(product_id, selprod_id);
    <?php } else { ?>
                productMembershipForm(product_id, selprod_id);
    <?php } ?>
        <?php } else { ?>
    productSaleDetails(product_id, selprod_id);
    <?php } ?>
    });
</script>
