<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?> <div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon">
                                <i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Manage_Tax', $adminLangId); ?> </h5> <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <section class="section searchform_filter">
                    <div class="sectionhead">
                        <h4> <?php echo Labels::getLabel('LBL_Search...', $adminLangId); ?></h4>
                    </div>
                    <div class="sectionbody space togglewrap" style="display:none;">
                        <?php
                            $frmSearch->setFormTagAttribute('onsubmit', 'searchRuleList(this); return(false);');
                            $frmSearch->setFormTagAttribute('class', 'web_form');
                            $frmSearch->developerTags['colClassPrefix'] = 'col-md-';
                            $frmSearch->developerTags['fld_default_col'] = 6;

                            $clrFld = $frmSearch->getField('btn_clear');
                            $clrFld->setFieldTagAttribute('onClick', 'clearSearch()');
                            echo  $frmSearch->getFormHtml();
                        ?>
                    </div>
                </section>
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_TAX_RULES', $adminLangId)." (".$taxCategory.")"; ?> </h4>
                        <?php
                        if ($canEdit) {
                            $data = [
                                'adminLangId' => $adminLangId,
                                'otherButtons' => [
                                    [
                                        'attr' => [
                                            'href' => 'javascript:void(0)',
                                            'onclick' => 'reloadList()',
                                            // 'class' => 'd-none',
                                            'id' => 'taxBackBtn',
                                            'title' => Labels::getLabel('LBL_BACK_TO_TAX_RULE', $adminLangId)
                                        ],
                                        'label' => '<i class="fas fa-arrow-left"></i>'
                                    ],
                                    [
                                        'attr' => [
                                            'href' => 'javascript:void(0)',
                                            'onclick' => 'ruleForm('.$taxCatId.')',
                                            'title' => Labels::getLabel('LBL_ADD_TAX_RULE', $adminLangId)
                                        ],
                                        'label' => '<i class="fas fa-plus"></i>'
                                    ],
                                ]
                            ];
        
                            $this->includeTemplate('_partial/action-buttons.php', $data, false);
                        }
                        ?>
                    </div>
                    <div class="sectionbody">
                        <div class="tablewrap">
                            <div id="taxListing"> <?php echo Labels::getLabel('LBL_Processing...', $adminLangId); ?> </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
