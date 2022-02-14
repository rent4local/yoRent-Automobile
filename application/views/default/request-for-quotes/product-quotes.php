<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>

<?php $this->includeTemplate('_partial/seller/sellerDashboardNavigation.php'); ?>
<main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
        <div class="content-header row">
            <div class="col">
                <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"><?php echo $pageTitle; ?></h2>
            </div>
            <!-- <div class="col-auto">
                <div class="btn-group">
                    <a href="<?php //echo CommonHelper::generateUrl('RequestForQuotes', 'listing'); ?>" class="btn btn-outline-brand btn--sm"><?php //echo Labels::getLabel('LBL_Back_to_offers_management', $siteLangId); ?></a>
                </div>
            </div> -->
        </div>
        <div class="content-body">
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            
                               
                                <?php
                                // $submitFld = $frmSearch->getField('btn_submit');
                                // $submitFld->setFieldTagAttribute('class', 'btn--block btn btn--primary');

                                // $fldClear= $frmSearch->getField('btn_clear');
                                // $fldClear->setFieldTagAttribute('class', 'btn--block btn btn-outline-brand');
                                $frmSearch->setFormTagAttribute('id', 'frmSearchAddonProduct');
                                $frmSearch->setFormTagAttribute('class', 'form');
                                $frmSearch->setFormTagAttribute('onsubmit', 'searchProductQuotes(this); return(false);');

                                $frmSearch->changeFieldPosition(7, 3);
                                
                                $frmSearch->getField('keyword')->addFieldTagAttribute('placeholder', Labels::getLabel('LBL_Search_by', $siteLangId));
                                $frmSearch->developerTags['colClassPrefix'] = 'col-md-';
                                $frmSearch->developerTags['fld_default_col'] = 12;

                                $keywordFld = $frmSearch->getField('keyword');
                                $keywordFld->setWrapperAttribute('class', 'col-lg-6');
                                $keywordFld->developerTags['col'] = 6;
                                $keywordFld->developerTags['noCaptionTag'] = true;

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

                                $typeFld = $frmSearch->getField('rfq_status');
                                $typeFld->setWrapperAttribute('class', 'col-lg-2');
                                $typeFld->developerTags['col'] = 2;
                                $typeFld->developerTags['noCaptionTag'] = true;

                                $typeFld = $frmSearch->getField('prod_type');
                                $typeFld->setWrapperAttribute('class', 'col-lg-2');
                                $typeFld->developerTags['col'] = 2;
                                $typeFld->developerTags['noCaptionTag'] = true;

                                $submitFld = $frmSearch->getField('btn_submit');
                                $submitFld->setFieldTagAttribute('class', 'btn-block btn btn-brand');
                                $submitFld->setWrapperAttribute('class', 'col-lg-2');
                                $submitFld->developerTags['col'] = 2;
                                $submitFld->developerTags['noCaptionTag'] = true;

                                $fldClear = $frmSearch->getField('btn_clear');
                                $fldClear->setFieldTagAttribute('onclick', 'clearSearch()');
                                $fldClear->setFieldTagAttribute('class', 'btn-block btn btn-outline-brand');
                                $fldClear->setWrapperAttribute('class', 'col-lg-2');
                                $fldClear->developerTags['col'] = 2;
                                $fldClear->developerTags['noCaptionTag'] = true;
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
