<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$frmSearch->setFormTagAttribute('class', 'form');
$frmSearch->setFormTagAttribute('onsubmit', 'searchUnavailbleDates(this); return(false);');
$frmSearch->developerTags['colClassPrefix'] = 'col-md-';
//$frmSearch->developerTags['fld_default_col'] = 8;

$keywordFld = $frmSearch->getField('keyword');
$keywordFld->developerTags['col'] = 8;
$keywordFld->developerTags['noCaptionTag'] = true;

if (0 < $selprod_id) {
    $keywordFld->setFieldTagAttribute('readonly', 'readonly');
}
$submitBtnFld = $frmSearch->getField('btn_submit');
$submitBtnFld->setFieldTagAttribute('class', 'btn btn-brand btn-block ');
$submitBtnFld->setWrapperAttribute('class', (0 < $selprod_id ? ' d-none' : ''));
$submitBtnFld->setWrapperAttribute('class', 'col-6');
$submitBtnFld->developerTags['col'] = 2;
$submitBtnFld->developerTags['noCaptionTag'] = true;

$cancelBtnFld = $frmSearch->getField('btn_clear');
$cancelBtnFld->setFieldTagAttribute('onclick', 'clearSearch(' . $selprod_id . ');');
$cancelBtnFld->setFieldTagAttribute('class', 'btn btn-outline-brand btn-block');
$cancelBtnFld->setWrapperAttribute('class', 'col-6');
$cancelBtnFld->developerTags['col'] = 2;
$cancelBtnFld->developerTags['noCaptionTag'] = true;
?>
<?php $this->includeTemplate('_partial/seller/sellerDashboardNavigation.php'); ?>
<main id="main-area" class="main"   >
    <div class="content-wrapper content-space">
        <div class="content-header row">
            <div class="col">
                <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Seller_Products_Rental_Unavailable_Dates', $siteLangId); ?><i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="<?php echo Labels::getLabel('LBL_Dates_when_the_product_will_remain_unavailable_for_rent.', $siteLangId); ?>"></i></h2>
                <small class="note">(<?php echo Labels::getLabel('LBL_Valid_Only_With_Rental_Products', $siteLangId); ?>)</small>
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
                        <?php if ($canEdit) { ?>
                            <div id="unavailabledates-form--js"></div>
                        <?php } ?>
                        <div class="card-header">
                            <div class="card-title"></div>
                            <div class="btn-group">
                                <a class="btn btn-outline-brand btn-sm formActionBtn-js formActions-css" title="<?php echo Labels::getLabel('LBL_Delete_Special_Price', $siteLangId); ?>" onclick="deleteSpecialPriceRows()" href="javascript:void(0)">							
                                    <?php echo Labels::getLabel('LBL_REMOVE', $siteLangId); ?>
                                </a>
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
