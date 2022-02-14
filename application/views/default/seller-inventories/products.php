<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
$frmSearch->setFormTagAttribute('onSubmit', 'sellerProducts(0,1,' . $prodType . '); return(false);');

$frmSearch->setFormTagAttribute('class', 'form');
$frmSearch->developerTags['colClassPrefix'] = 'col-md-';

$keyFld = $frmSearch->getField('keyword');
$keyFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Keyword', $siteLangId));
$keyFld->developerTags['col'] = 8;
$keyFld->developerTags['noCaptionTag'] = true;

$submitBtnFld = $frmSearch->getField('btn_submit');
$submitBtnFld->setFieldTagAttribute('class', 'btn-block');
$submitBtnFld->setWrapperAttribute('class', 'col-6');
$submitBtnFld->developerTags['col'] = 2;
$submitBtnFld->developerTags['noCaptionTag'] = true;

$cancelBtnFld = $frmSearch->getField('btn_clear');
$cancelBtnFld->setFieldTagAttribute('class', 'btn-block');
$cancelBtnFld->setWrapperAttribute('class', 'col-6');
$cancelBtnFld->developerTags['col'] = 2;
$cancelBtnFld->developerTags['noCaptionTag'] = true;?>
<?php $this->includeTemplate('_partial/seller/sellerDashboardNavigation.php'); ?>
<main id="main-area" class="main"   >
    <div class="content-wrapper content-space">
        <div class="content-header row justify-content-between mb-3 ffdgdf">
            <?php //$this->includeTemplate('_partial/dashboardTop.php'); ?>
            <div class="col-md-auto">
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_My_Inventory', $siteLangId); ?><i class="fa fa-question-circle" onClick="productInstructions(<?php echo Extrapage::SELLER_INVENTORY_INSTRUCTIONS; ?>)"></i></h2>
            </div>
            <?php $this->includeTemplate('_partial/productPagesTabs.php', array('siteLangId' => $siteLangId, 'controllerName' => $controllerName, 'action' => $action, 'canEdit' => $canEdit, 'adminCatalogs' => $adminCatalogs), false); ?>
        </div>
        <div class="content-body">
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="replaced">
                                <?php
                                $submitFld = $frmSearch->getField('btn_submit');
                                $submitFld->setFieldTagAttribute('class', 'btn btn-brand btn-block ');

                                $fldClear = $frmSearch->getField('btn_clear');
                                $fldClear->setFieldTagAttribute('class', 'btn btn-outline-brand btn-block');

                                echo $frmSearch->getFormHtml();
                                ?>
                                <?php echo $frmSearch->getExternalJS(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title"></div>
                            
                            <div class="btn-group">
                            <?php if ($canEdit) { ?>
                                <a class="btn btn-outline-brand btn-sm formActionBtnJs disabled" title="<?php echo Labels::getLabel('LBL_Activate', $siteLangId); ?>" onclick="toggleBulkStatues(1)" href="javascript:void(0)"><?php echo Labels::getLabel('LBL_Activate', $siteLangId); ?></a>
                                <a class="btn btn-outline-brand btn-sm formActionBtnJs disabled" title="<?php echo Labels::getLabel('LBL_Deactivate', $siteLangId); ?>" onclick="toggleBulkStatues(0)" href="javascript:void(0)"><?php echo Labels::getLabel('LBL_Deactivate', $siteLangId); ?></a>
                                <a class="btn btn-outline-brand btn-sm formActionBtnJs disabled" title="<?php echo Labels::getLabel('LBL_Delete', $siteLangId); ?>" onclick="deleteBulkSellerProducts()" href="javascript:void(0)"><?php echo Labels::getLabel('LBL_Delete', $siteLangId); ?></a>
                            <?php } ?>    
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="listing">
                                <?php echo Labels::getLabel('LBL_Loading..', $siteLangId); ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php echo FatUtility::createHiddenFormFromData(array('product_id' => $product_id, 'prodType' => $prodType), array('name' => 'frmSearchSellerProducts'));?>
<script>
    var SELPROD_TYPE = <?php echo $prodType;?>
</script>

<script>
    jQuery(document).ready(function($) {
        $(".initTooltip").click(function() {
            $.facebox({
                div: '#inventoryToolTip'
            }, 'catalog-bg');
        });
    });
</script>