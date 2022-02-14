<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php $this->includeTemplate('_partial/seller/sellerDashboardNavigation.php'); ?>
<?php
$frmSearch->setFormTagAttribute('class', 'form ');
$frmSearch->setFormTagAttribute('onsubmit', 'search(this); return(false);');
$frmSearch->developerTags['colClassPrefix'] = 'col-md-';
$frmSearch->developerTags['fld_default_col'] = 4;

$startDate = $frmSearch->getField('start_date');
$startDate->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Start_Date', $siteLangId));
$startDate->setFieldTagAttribute('class', 'date_js');
$startDate->setFieldTagAttribute('readonly', 'readonly');
$startDate->developerTags['noCaptionTag'] = true;

$endDate = $frmSearch->getField('end_date');
$endDate->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_End_Date', $siteLangId));
$endDate->setFieldTagAttribute('class', 'date_js');
$endDate->setFieldTagAttribute('readonly', 'readonly');
$endDate->developerTags['noCaptionTag'] = true;

$submitBtnFld = $frmSearch->getField('btn_submit');
$submitBtnFld->setFieldTagAttribute('class', 'btn btn-brand btn-block');
$submitBtnFld->developerTags['col'] = 2;
$submitBtnFld->developerTags['noCaptionTag'] = true;

$cancelBtnFld = $frmSearch->getField('btn_clear');
$cancelBtnFld->setFieldTagAttribute('onclick', 'clearSearch();');
$cancelBtnFld->setFieldTagAttribute('class', 'btn btn-outline-brand btn-block');
$cancelBtnFld->developerTags['col'] = 2;
$cancelBtnFld->developerTags['noCaptionTag'] = true;
?>
<?php $this->includeTemplate('_partial/seller/sellerDashboardNavigation.php'); ?>
<main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
        <div class="content-header row">
            <div class="col">
                <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Product_Returns', $siteLangId); ?></h2>
                <div class="tabs tabs--small clearfix">
                    <ul>
                        <li class="<?php
                        if (FatApp::getAction() == 'upcomingProductReturns') {
                            echo "is-active";
                        }
                        ?>">
                            <a href="<?php echo CommonHelper::generateUrl('ProductReturns', 'upcomingProductReturns'); ?>"> <?php echo Labels::getLabel('LBL_Upcoming_product_returns', $siteLangId); ?> </a>
                        </li>
                        <li class="<?php
                        if (FatApp::getAction() == 'overdueProductReturns') {
                            echo "is-active";
                        }
                        ?>">
                            <a href="<?php echo CommonHelper::generateUrl('ProductReturns', 'overdueProductReturns'); ?>"> <?php echo Labels::getLabel('LBL_Overdue_returns', $siteLangId); ?> </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="content-body">
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="replaced">
                                <?php echo $frmSearch->getFormHtml(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div id="ordersListing"></div>
                            <span class="gap"></span>
                            <?php /* <div id="loaderImage" style="display: none;">
                              <div class="loader"></div>
                              </div> */
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
    $(document).ready(function () {
        $('.date_js').datepicker('option', {maxDate: new Date()});
    });
</script>
