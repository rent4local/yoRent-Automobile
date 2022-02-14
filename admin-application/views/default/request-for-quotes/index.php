<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php

$frmSearch->setFormTagAttribute('onsubmit', 'searchRFQ(this,1); return(false);');
$frmSearch->setFormTagAttribute('class', 'web_form');
$frmSearch->developerTags['colClassPrefix'] = 'col-md-';
$frmSearch->developerTags['fld_default_col'] = 3;

$keyFld = $frmSearch->getField('keyword');
$keyFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Keyword', $adminLangId));
$keyFld->setWrapperAttribute('class', 'col-lg-2');
$keyFld->developerTags['col'] = 2;
$keyFld->developerTags['noCaptionTag'] = true;

$keyFld = $frmSearch->getField('request_from_date');
$keyFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Date_From', $adminLangId));
$keyFld->setWrapperAttribute('class', 'col-lg-2');
$keyFld->developerTags['col'] = 2;
$keyFld->developerTags['noCaptionTag'] = true;

$keyFld = $frmSearch->getField('request_to_date');
$keyFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Date_To', $adminLangId));
$keyFld->setWrapperAttribute('class', 'col-lg-2');
$keyFld->developerTags['col'] = 2;
$keyFld->developerTags['noCaptionTag'] = true;

$keyFld = $frmSearch->getField('rfq_status');
$keyFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Select_Status', $adminLangId));
$keyFld->setWrapperAttribute('class', 'col-lg-2');
$keyFld->developerTags['col'] = 2;
$keyFld->developerTags['noCaptionTag'] = true;

$keyFld = $frmSearch->getField('prod_type');
$keyFld->setWrapperAttribute('class', 'col-lg-2');
$keyFld->developerTags['col'] = 2;
$keyFld->developerTags['noCaptionTag'] = true;

$submitBtnFld = $frmSearch->getField('btn_submit');
$submitBtnFld->setFieldTagAttribute('class', 'btn--block');
$submitBtnFld->setWrapperAttribute('class', 'col-lg-1');
$submitBtnFld->developerTags['col'] = 1;
$submitBtnFld->developerTags['noCaptionTag'] = true;

$cancelBtnFld = $frmSearch->getField('btn_clear');
$cancelBtnFld->setFieldTagAttribute('class', 'btn--block');
$cancelBtnFld->setWrapperAttribute('class', 'col-lg-1');
$cancelBtnFld->developerTags['col'] = 1;
$cancelBtnFld->developerTags['noCaptionTag'] = true;

?>
<div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon">
                                <i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_RFQ_MANAGEMENT', $adminLangId); ?> </h5>
                            <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <section class="section searchform_filter">
                    <div class="sectionhead">
                        <h4> <?php echo Labels::getLabel('LBL_Search...', $adminLangId); ?></h4>
                    </div>
                    <div class="sectionbody space togglewrap" style="display:none;">
                        <?php
                        echo $frmSearch->getFormHtml();
                        ?>
                    </div>
                </section>

                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_RFQ_List', $adminLangId); ?> </h4>
                    </div>
                    <div class="sectionbody">
                        <div class="tablewrap">
                            <div id="rfq_listing">
                                <?php echo Labels::getLabel('LBL_Processing...', $adminLangId); ?>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>