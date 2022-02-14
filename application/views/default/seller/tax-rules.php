<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php $this->includeTemplate('_partial/seller/sellerDashboardNavigation.php'); ?>
<main id="main-area" class="main"   >
    <div class="content-wrapper content-space">
        <div class="content-header row">
            <div class="col">
                <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"><?php echo $taxCategory; ?></h2>
            </div>
            <div class="col-auto">
                <div class="btn-group">
                    <a href="<?php echo UrlHelper::generateUrl('seller', 'taxCategories'); ?>"
                       class="btn btn-outline-brand btn-sm">
                           <?php echo Labels::getLabel('LBL_Back_To_Tax_Categories', $siteLangId) ?>
                    </a>
                </div>
            </div>
        </div>
        <div class="content-body">
            <div class="card">
                <?php echo $frmSearch->getFormHtml(); ?>
                <div class="card-body" id="listing">                   
                </div>
            </div>
        </div>
    </div>
</main>
