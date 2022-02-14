<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="page">
    <div class="container container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row justify-content-between">
                        <div class="col--first col-lg-6">
                            <span class="page__icon"><i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Manage_Catalog', $adminLangId); ?> </h5>
                            <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <div class="tabs_nav_container wizard-tabs-horizontal">
                    <ul class="tabs_nav">
                        <li><a class="active tabs_001" rel="tabs_001" href="javascript:void(0)" <?php echo ($preqId) ? "onClick='productForm( " . $preqId . ", 0 );'" : ""; ?>>
                                <div class="tabs-head">
                                    <div class="tabs-title"><?php echo Labels::getLabel('LBL_Initial_Setup', $adminLangId); ?><span><?php echo Labels::getLabel('LBL_Setup_Basic_Details', $adminLangId); ?></span></div>
                                </div>
                            </a>
                        </li>
                        <li><a rel="tabs_002" class="tabs_002" href="javascript:void(0)" <?php echo (0 < $preqId) ? "onclick='customCatalogSpecifications( " . $preqId . " );'" : ""; ?>>
                                <div class="tabs-head">
                                    <div class="tabs-title"><?php echo Labels::getLabel('LBL_Attribute_&_Specifications', $adminLangId); ?><span><?php echo Labels::getLabel('LBL_Add_Attribute_&_Specifications', $adminLangId); ?></span></div>
                                </div>
                            </a>
                        </li>
                        <li class="<?php echo (0 == $preqId) ? 'fat-inactive' : ''; ?>">
                            <a rel="tabs_003" class="tabs_003" href="javascript:void(0);" <?php echo (0 < $preqId) ? "onclick='productOptionsAndTag(" . $preqId . ");'" : ""; ?>>
                                <div class="tabs-head">
                                    <div class="tabs-title">
                                        <?php echo Labels::getLabel('LBL_Options_and_Tags', $adminLangId); ?><span><?php echo Labels::getLabel('LBL_Add_Options_and_Tags', $adminLangId); ?></span>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                                <a rel="tabs_004" class="tabs_004" href="javascript:void(0);" <?php echo ($preqId) ? "onClick='productShipping(" . $preqId . ");'" : ""; ?>>
                                    <div class="tabs-head">
                                        <div class="tabs-title">
                                            <?php echo Labels::getLabel('LBL_Shipping_Information', $adminLangId); ?>
                                            <span><?php echo Labels::getLabel('LBL_Shipping_Information', $adminLangId); ?></span>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        <?php /* if (count($productOptions) > 0) { ?>
                            <li>
                                <a rel="tabs_004" class="tabs_004" href="javascript:void(0);" <?php echo ($preqId) ? "onClick='customEanUpcForm(" . $preqId . ");'" : ""; ?>>
                                    <div class="tabs-head">
                                        <div class="tabs-title">
                                            <?php echo Labels::getLabel('LBL_EAN/UPC_setup', $adminLangId); ?>
                                            <span><?php echo Labels::getLabel('LBL_Add_EAN/UPC_setup', $adminLangId); ?></span>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        <?php } */ ?>
                        <?php if (FatApp::getConfig('CONF_USE_CUSTOM_FIELDS', FatUtility::VAR_INT, 0) == applicationConstants::YES) { ?>
                            <li>
                                <a rel="tabs_005" class="tabs_005 <?php echo (!$isCustomFields) ? "disabled" : ""; ?>" <?php echo ($preqId) ? "onClick='productCatCustomFields( " . $preqId . ");'" : ""; ?> href="javascript:void(0);">
                                    <div class="tabs-head">
                                        <div class="tabs-title">
                                            <?php echo Labels::getLabel('LBL_Custom_fields', $adminLangId); ?>
                                            <span><?php echo Labels::getLabel('LBL_Add_Custom_fields_Data_Based_on_Category', $adminLangId); ?></span>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        <?php } ?>
                        <li>
                            <a rel="tabs_006" class="tabs_006" <?php echo ($preqId) ? "onClick='customCatalogProductImages( " . $preqId . ");'" : ""; ?> href="javascript:void(0);">
                                <div class="tabs-head">
                                    <div class="tabs-title">
                                        <?php echo Labels::getLabel('LBL_Media', $adminLangId); ?>
                                        <span><?php echo Labels::getLabel('LBL_Add_Media', $adminLangId); ?></span>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a rel="tabs_007" class="tabs_007" <?php echo ($preqId) ? "onClick='updateStatusForm( " . $preqId . ");'" : ""; ?> href="javascript:void(0);">
                                <div class="tabs-head">
                                    <div class="tabs-title">
                                        <?php echo Labels::getLabel('LBL_Change_Status', $adminLangId); ?>
                                        <span><?php echo Labels::getLabel('LBL_Add_Change_Status', $adminLangId); ?></span>
                                    </div>
                                </div>
                            </a>
                        </li>
                    </ul>

                    <div class="tabs_panel_wrap">
                        <div id="tabs_001" class="tabs_panel" style="display: block;"></div>
                        <div id="tabs_002" class="tabs_panel" style="display: none;"> </div>
                        <div id="tabs_003" class="tabs_panel" style="display: none;"></div>
                        <div id="tabs_004" class="tabs_panel" style="display: none;"></div>
                        <div id="tabs_005" class="tabs_panel" style="display: none;"></div>
                        <div id="tabs_006" class="tabs_panel" style="display: none;"></div>
                        <div id="tabs_007" class="tabs_panel" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    productForm(<?php echo $preqId; ?>);
</script>

<style>
.disabled {
    pointer-events: none;
    color: #25212133 !important;
}
</style>