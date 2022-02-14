<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon"><i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Bad_Categories_Report', $adminLangId); ?>
                            </h5>
                            <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>

                <!--<div class="row">
	<div class="col-sm-12">-->
                <section class="section searchform_filter">
                    <div class="sectionhead">
                        <h4> <?php echo Labels::getLabel('LBL_Search', $adminLangId); ?>
                        </h4>
                    </div>
                    <div class="sectionbody space togglewrap" style="display:none;">
                        <?php
                            $frmSearch->setFormTagAttribute('onsubmit', 'searchBadCategoriesReport(this); return(false);');
                            $frmSearch->setFormTagAttribute('class', 'web_form');
                            $frmSearch->developerTags['colClassPrefix'] = 'col-md-';
                            $frmSearch->developerTags['fld_default_col'] = 6;
                            echo  $frmSearch->getFormHtml();
                        ?>
                    </div>
                </section>
                <!--</div>
	<div class="col-sm-12">-->
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Bad_Categories_Report', $adminLangId); ?></h4>
                        <strong class="text-danger"><?php echo Labels::getLabel('LBL_Note', $adminLangId); ?> :: <?php echo Labels::getLabel('LBL_We_have_not_considered_impact_of_cancels_in_this_report.', $adminLangId); ?></strong>
                        <?php
                            $data = [
                                'adminLangId' => $adminLangId,
                                'statusButtons' => false,
                                'deleteButton' => false,
                                'otherButtons' => [
                                    [
                                        'attr' => [
                                            'href' => 'javascript:void(0)',
                                            'onclick' => 'exportReport()',
                                            'title' => Labels::getLabel('LBL_Export', $adminLangId)
                                        ],
                                        'label' => '<i class="fas fa-file-export"></i>'
                                    ],
                                ]
                            ];
        
                            $this->includeTemplate('_partial/action-buttons.php', $data, false);
                        ?>
                        <!--<a href="javascript:void(0)" class="themebtn btn-default btn-sm" onClick="exportReport()"><?php echo Labels::getLabel('LBL_Export', $adminLangId); ?></a>-->
                    </div>
                    <div class="sectionbody">
                        <div class="tablewrap">
                            <div id="listing"> <?php echo Labels::getLabel('LBL_Processing...', $adminLangId); ?>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>