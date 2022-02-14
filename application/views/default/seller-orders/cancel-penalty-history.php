<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?> 
<?php $this->includeTemplate('_partial/seller/sellerDashboardNavigation.php'); ?> 
<main id="main-area" class="main">
    <div class="content-wrapper content-space">
        <div class="content-header row">
            <div class="col">
                <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"> 
                    <?php echo Labels::getLabel('LBL_Rental_Order_Cancel_Penalty_History', $siteLangId); ?>
                </h2>
            </div>
        </div>
        <div class="content-body">
                <div class="row mb-4">
                    <div class="col-lg-12">  
                        <div class="card">
                            <div class="card-body">
                                <?php
                                $frmSearch->setFormTagAttribute('onSubmit', 'searchHistory(this); return(false);');
                                $frmSearch->setFormTagAttribute('class', 'form');
                                $frmSearch->developerTags['colClassPrefix'] = 'col-md-';
                                $frmSearch->developerTags['fld_default_col'] = 3;
                                
                                $keyFld = $frmSearch->getField('keyword');
                                $keyFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Keyword', $siteLangId));
                                $keyFld->developerTags['col'] = 8;
                                $keyFld->developerTags['noCaptionTag'] = true;
                                
                                $submitBtnFld = $frmSearch->getField('btn_submit');
                                $submitBtnFld->setFieldTagAttribute('class', 'btn btn-brand btn-block');
                                $submitBtnFld->setWrapperAttribute('class', 'col-6');
                                $submitBtnFld->developerTags['col'] = 2;
                                $submitBtnFld->developerTags['noCaptionTag'] = true;
                                
                                $cancelBtnFld = $frmSearch->getField('btn_clear');
                                $cancelBtnFld->setFieldTagAttribute('class', 'btn btn-outline-brand btn-block');
                                $cancelBtnFld->setFieldTagAttribute('onClick', 'clearSearch();');
                                $cancelBtnFld->setWrapperAttribute('class', 'col-6');
                                $cancelBtnFld->developerTags['col'] = 2;
                                $cancelBtnFld->developerTags['noCaptionTag'] = true;
                                
                                echo $frmSearch->getFormHtml();
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
        
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div id="chargesListing"></div>
                                <span class="gap"></span>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</main>
