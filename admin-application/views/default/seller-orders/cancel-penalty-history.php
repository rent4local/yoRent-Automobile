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
                            <h5><?php echo Labels::getLabel('LBL_Rental_Order_Cancel_Penalty_History', $adminLangId); ?> </h5>
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
                        $frmSearch->setFormTagAttribute('onsubmit', 'searchHistory(this); return(false);');
                        $frmSearch->setFormTagAttribute('class', 'web_form');
                        $frmSearch->developerTags['colClassPrefix'] = 'col-md-';
                        $frmSearch->developerTags['fld_default_col'] = 4;

                        $keywordFld = $frmSearch->getField('keyword');
                        $keywordFld->htmlAfterField = '<small>' . Labels::getLabel('LBL_Search_in_Order_id,_Invoice_Number', $adminLangId) . '</small>';

                        $buyerFld = $frmSearch->getField('buyer');
                        $buyerFld->htmlAfterField = '<small></small>';

                        $shopFld = $frmSearch->getField('shop_name');
                        $shopFld->htmlAfterField = '<small>' . Labels::getLabel('LBL_Search_in_Shop_Name,_Seller_Name,_Seller_UserName_and_Seller_EmailId,_Seller_Phone', $adminLangId) . '</small>';

                        $submitBtnFld = $frmSearch->getField('btn_submit');
                        $submitBtnFld->setFieldTagAttribute('class', 'btn');
                        $submitBtnFld->developerTags['col'] = 4;

                        $btn_clear = $frmSearch->getField('btn_clear');
                        $btn_clear->addFieldTagAttribute('onclick', 'clearSearch()');
                        echo  $frmSearch->getFormHtml();
                        ?>
                    </div>
                </section>
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Rental_Order_Late_Charges_History', $adminLangId); ?> </h4>
                    </div>
                    <div class="sectionbody">
                        <div class="tablewrap">
                            <div id="chargesListing">
                                <?php echo Labels::getLabel('LBL_Processing...', $adminLangId); ?>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
<script>
$(function () {
  $('[data-toggle="tooltip"]').tooltip()
})    
</script>
