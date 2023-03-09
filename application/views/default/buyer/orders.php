<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');

$frmOrderSrch->setFormTagAttribute('onSubmit', 'searchOrders(this); return false;');
$frmOrderSrch->setFormTagAttribute('class', 'form');
$frmOrderSrch->developerTags['colClassPrefix'] = 'col-md-';
$frmOrderSrch->developerTags['fld_default_col'] = 12;

$keywordFld = $frmOrderSrch->getField('keyword');
$keywordFld->setWrapperAttribute('class', 'col-lg-4');
$keywordFld->developerTags['col'] = 4;
$keywordFld->developerTags['noCaptionTag'] = true;
/* $keywordFld->htmlAfterField = '<small class="form-text text-muted">'.Labels::getLabel('LBL_Buyer_account_orders_listing_search_form_keyword_help_txt', $siteLangId).'</small>'; */

$statusFld = $frmOrderSrch->getField('status');
$statusFld->setWrapperAttribute('class', 'col-lg-4');
$statusFld->developerTags['col'] = 4;
$statusFld->developerTags['noCaptionTag'] = true;

$dateFromFld = $frmOrderSrch->getField('date_from');
$dateFromFld->setFieldTagAttribute('class', 'field--calender');
$dateFromFld->setWrapperAttribute('class', 'col-lg-2');
$dateFromFld->developerTags['col'] = 2;
$dateFromFld->developerTags['noCaptionTag'] = true;

$dateToFld = $frmOrderSrch->getField('date_to');
$dateToFld->setFieldTagAttribute('class', 'field--calender');
$dateToFld->setWrapperAttribute('class', 'col-lg-2');
$dateToFld->developerTags['col'] = 2;
$dateToFld->developerTags['noCaptionTag'] = true;

$priceFromFld = $frmOrderSrch->getField('price_from');
$priceFromFld->setWrapperAttribute('class', 'col-lg-2');
$priceFromFld->developerTags['col'] = 2;
$priceFromFld->developerTags['noCaptionTag'] = true;

$priceToFld = $frmOrderSrch->getField('price_to');
$priceToFld->setWrapperAttribute('class', 'col-lg-2');
$priceToFld->developerTags['col'] = 2;
$priceToFld->developerTags['noCaptionTag'] = true;

$submitBtnFld = $frmOrderSrch->getField('btn_submit');
$submitBtnFld->setFieldTagAttribute('class', 'btn btn-brand btn-block ');
$submitBtnFld->setWrapperAttribute('class', 'col-lg-2');
$submitBtnFld->developerTags['col'] = 2;
$submitBtnFld->developerTags['noCaptionTag'] = true;

$cancelBtnFld = $frmOrderSrch->getField('btn_clear');
$cancelBtnFld->setFieldTagAttribute('class', 'btn btn-outline-brand btn-block');
$cancelBtnFld->setWrapperAttribute('class', 'col-lg-2');
$cancelBtnFld->developerTags['col'] = 2;
$cancelBtnFld->developerTags['noCaptionTag'] = true;
?> <?php $this->includeTemplate('_partial/buyerDashboardNavigation.php'); ?> <main id="main-area" class="main"   >
    <div class="content-wrapper content-space">
        <div class="content-header row">
            <div class="col">
                <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <?php $orderLabel = ($orderType == applicationConstants::PRODUCT_FOR_SALE) ? 'LBL_Purchased_Order_History' : 'LBL_Rental_Order_History'; ?>
                <h2 class="content-header-title"> <?php echo Labels::getLabel($orderLabel, $siteLangId); ?></h2>
                <?php /* <div class="tabs tabs--small clearfix">
                  <ul>
                  <li class="<?php echo ($orderType == applicationConstants::PRODUCT_FOR_SALE) ? 'is-active' : ''; ?>">
                  <a href="<?php echo UrlHelper::generateUrl('Buyer', 'orders'); ?>"> <?php echo Labels::getLabel('LBL_Purchased_orders', $siteLangId); ?> </a>
                  </li>
                  <li class="<?php echo ($orderType == applicationConstants::PRODUCT_FOR_RENT) ? 'is-active' : ''; ?>">
                  <a href="<?php echo UrlHelper::generateUrl('Buyer', 'RentalOrders'); ?>"> <?php echo Labels::getLabel('LBL_Rented_orders', $siteLangId); ?> </a>
                  </li>
                  </ul>
                  </div> */ ?>
            </div>
        </div>
        <div class="content-body">
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="replaced">
                                <?php echo $frmOrderSrch->getFormHtml(); ?>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
