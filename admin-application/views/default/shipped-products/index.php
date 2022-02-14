<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>

<div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon">
                                <i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Shipped_Products_by_Admin', $adminLangId); ?> </h5>
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
                        $frmSearch->setFormTagAttribute('onsubmit', 'searchShippedProducts(this, 1); return(false);');
                        $frmSearch->setFormTagAttribute('class', 'web_form');
                        $frmSearch->developerTags['colClassPrefix'] = 'col-md-';
                        $frmSearch->developerTags['fld_default_col'] = 4;

                        $keywordFld = $frmSearch->getField('keyword');
                        $keywordFld->developerTags['col'] = 4;

                        $submitBtnFld = $frmSearch->getField('btn_submit');
                        $submitBtnFld->setFieldTagAttribute('class', 'btn');
                        $submitBtnFld->developerTags['col'] = 4;

                        $btn_clear = $frmSearch->getField('btn_clear');
                        $btn_clear->addFieldTagAttribute('onclick', 'clearShippedProductsSearch()');
                        echo  $frmSearch->getFormHtml();
                        ?>
                    </div>
                </section>

                <section class="section">
                    <div class="tablewrap">
                        <div id="shippedProductsListing">
                            <?php echo Labels::getLabel('LBL_Processing...', $adminLangId); ?>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>