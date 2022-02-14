<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frmSearch->setFormTagAttribute('id', 'frmSearchQuotesRequests');
$frmSearch->setFormTagAttribute('onsubmit', 'searchQuotedRequests(this); return(false);');
$frmSearch->changeFieldPosition(7, 3);

$frmSearch->setFormTagAttribute('class', 'form');
$frmSearch->developerTags['colClassPrefix'] = 'col-md-';
$frmSearch->developerTags['fld_default_col'] = 12;
$frmSearch->developerTags['noCaptionTag'] = true;

$keyFld = $frmSearch->getField('keyword');
$keyFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Keyword', $siteLangId));
$keyFld->setWrapperAttribute('class', 'col-lg-4');
$keyFld->developerTags['col'] = 4;
$keyFld->developerTags['noCaptionTag'] = true;

$keyFld = $frmSearch->getField('request_from_date');
$keyFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Date_From', $siteLangId));
$keyFld->setWrapperAttribute('class', 'col-lg-2');
$keyFld->developerTags['col'] = 2;
$keyFld->developerTags['noCaptionTag'] = true;

$keyFld = $frmSearch->getField('request_to_date');
$keyFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Date_To', $siteLangId));
$keyFld->setWrapperAttribute('class', 'col-lg-2');
$keyFld->developerTags['col'] = 2;
$keyFld->developerTags['noCaptionTag'] = true;


$keyFld = $frmSearch->getField('rfq_status');
$keyFld->setWrapperAttribute('class', 'col-lg-2');
$keyFld->developerTags['col'] = 2;
$keyFld->developerTags['noCaptionTag'] = true;

$keyFld = $frmSearch->getField('prod_type');
$keyFld->setWrapperAttribute('class', 'col-lg-2');
$keyFld->developerTags['col'] = 2;
$keyFld->developerTags['noCaptionTag'] = true;

$submitBtnFld = $frmSearch->getField('btn_submit');
$submitBtnFld->setFieldTagAttribute('class', 'btn--block');
$submitBtnFld->setWrapperAttribute('class', 'col-lg-2');
$submitBtnFld->developerTags['col'] = 2;
$submitBtnFld->developerTags['noCaptionTag'] = true;

$cancelBtnFld = $frmSearch->getField('btn_clear');
$cancelBtnFld->setFieldTagAttribute('class', 'btn--block');
$cancelBtnFld->setWrapperAttribute('class', 'col-lg-2');
$cancelBtnFld->developerTags['col'] = 2;
$cancelBtnFld->developerTags['noCaptionTag'] = true;
?>

<?php $this->includeTemplate('_partial/seller/sellerDashboardNavigation.php'); ?>

<main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
        <div class="content-header row">
            <div class="col">
                <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Re-Quoted_Offers', $siteLangId); ?></h2>
            </div>
        </div>
        <div class="content-body">
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="replaced">
                               
                                <?php
                                $submitFld = $frmSearch->getField('btn_submit');
                                $submitFld->setFieldTagAttribute('class', 'btn-block btn btn-brand');
                                $submitFld->setWrapperAttribute('class', 'col-lg-2');
                                $submitFld->developerTags['col'] = 2;

                                $fldClear = $frmSearch->getField('btn_clear');
                                $fldClear->setFieldTagAttribute('onclick', 'clearSearch()');
                                $fldClear->setFieldTagAttribute('class', 'btn-block btn btn-outline-brand');
                                $fldClear->setWrapperAttribute('class', 'col-lg-2');
                                $fldClear->developerTags['col'] = 2;
                                echo $frmSearch->getFormHtml();
                                ?>
                                    
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div id="listing"><?php echo Labels::getLabel('LBL_Loading..', $siteLangId); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>