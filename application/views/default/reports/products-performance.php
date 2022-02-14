<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?> <?php $this->includeTemplate('_partial/dashboardNavigation.php'); ?> <main id="main-area" class="main"   >
    <div class="content-wrapper content-space">
        <div class="content-header row">
            <div class="col"> <?php $this->includeTemplate('_partial/dashboardTop.php'); ?> 
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Products_Performance_Report', $siteLangId);?></h2>
            </div>
            <div class="col-auto"> 
                <div class="btn-group"> 
                    <?php if ($reportType == 3) { ?>
                        <a href="javascript:void(0)" id="performanceReportExport" onclick="exportMostFavProdReport()" class="btn btn-outline-brand btn-sm"><?php echo Labels::getLabel('LBL_Export', $siteLangId);?></a>
                    <?php } else { ?>
                        <a href="javascript:void(0)" id="performanceReportExport" onclick="exportProdPerformanceReport(<?php echo $reportType;?>)" class="btn btn-outline-brand btn-sm"><?php echo Labels::getLabel('LBL_Export', $siteLangId);?></a>
                    <?php } ?>
                </div>
            </div>
        </div>
        <div class="content-body"> 
            <div class="tabs tabs--small tabs--scroll setactive-js">
                <ul>
                    <li class="<?php echo ($reportType == 1) ? "is-active" : "";?>"><a href="<?php echo UrlHelper::generateUrl('Reports', 'ProductsPerformance', [1]); ?>"><?php echo Labels::getLabel('LBL_Top_Performing_Products', $siteLangId);?></a></li>
                    <li class="<?php echo ($reportType == 2) ? "is-active" : "";?>"><a href="<?php echo UrlHelper::generateUrl('Reports', 'ProductsPerformance', [2]); ?>"><?php echo Labels::getLabel('LBL_Most_Refunded_Products_Report', $siteLangId);?></a></li>
                    <li class="<?php echo ($reportType == 3) ? "is-active" : "";?>"><a href="<?php echo UrlHelper::generateUrl('Reports', 'ProductsPerformance', [3]); ?>" ><?php echo Labels::getLabel('LBL_Most_WishList_Added_Products', $siteLangId);?></a></li>
                </ul>
            </div>
            <?php if ($reportType != 3) { ?>
            <div class="row mb-4 search-section--js">
                <div class="col-lg-12"> 
                    <div class="card">
                        <div class="card-body">
                            <div class="replaced">
                                <?php
                                $srchFrm->setFormTagAttribute('onSubmit', 'productPerformanceSrch(this); return false;');
                                $srchFrm->setFormTagAttribute('class', 'form');
                                $srchFrm->developerTags['colClassPrefix'] = 'col-lg-3 col-md-';
                                $srchFrm->developerTags['fld_default_col'] = 3;
                                
                                $sortFld = $srchFrm->getField('sort_by');
                                $sortFld->developerTags['noCaptionTag'] = true;
                                
                                $dateFrm = $srchFrm->getField('date_from');
                                $dateFrm->developerTags['noCaptionTag'] = true;
            
                                $dateTo = $srchFrm->getField('date_to');
                                $dateTo->developerTags['noCaptionTag'] = true;
            
                                $submitFld = $srchFrm->getField('btn_submit');
                                $submitFld->developerTags['noCaptionTag'] = true;
                                $submitFld->setFieldTagAttribute('class', 'btn btn-brand btn-block ');
            
                                $fldClear = $srchFrm->getField('btn_clear');
                                $fldClear->setFieldTagAttribute('class', 'btn btn-outline-brand btn-block');
                                $fldClear->developerTags['noCaptionTag'] = true;
                                echo $srchFrm->getFormHtml();
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">                      
                       <div class="card-body ">
                       <div id="listingDiv"> <?php echo Labels::getLabel('LBL_Loading..', $siteLangId); ?> </div>
                       </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    var REPORT_TYPE = <?php echo $reportType?>;
</script>    
