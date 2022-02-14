<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?> <div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon"><i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Manage_Content_Pages', $adminLangId); ?> </h5> <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <section class="section searchform_filter">
                    <div class="sectionhead">
                        <h4> <?php echo Labels::getLabel('LBL_Search...', $adminLangId); ?></h4>
                    </div>
                    <div class="sectionbody space togglewrap" style="display:none;">
                        <?php
                            $frmSearch->setFormTagAttribute('onsubmit', 'searchPages(this); return(false);');
                            $frmSearch->setFormTagAttribute('class', 'web_form');
                            $frmSearch->developerTags['colClassPrefix'] = 'col-md-';
                            $frmSearch->developerTags['fld_default_col'] = 6;
                            $btn_clear = $frmSearch->getField('btn_clear');
                            $btn_clear->addFieldTagAttribute('onclick', 'clearSearch()');
                            echo  $frmSearch->getFormHtml();
                        ?>
                    </div>
                </section>
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Content_Pages', $adminLangId); ?></h4> 
                        <?php
                        $data = [
                            'adminLangId' => $adminLangId,
                            'statusButtons' => false,
                            'deleteButton' => $canEdit,
                        ];
                        if ($canEdit) {
                            $data['otherButtons'][] = [
                                'attr' => [
                                    'href' => 'javascript:void(0)',
                                    'onclick' => 'addFormNew(0)',
                                    'title' => Labels::getLabel('LBL_Add_Page', $adminLangId)
                                ],
                                'label' => '<i class="fas fa-plus"></i>'
                            ];
                        }
                        $data['otherButtons'][] = [
                            'attr' => [
                                'href' => 'javascript:void(0)',
                                'onclick' => 'pagesLayouts()',
                                'title' => Labels::getLabel('Lbl_Layouts_Instructions', $adminLangId)
                            ],
                            'label' => '<i class="fas fa-file-image"></i>'
                        ];
    
                        $this->includeTemplate('_partial/action-buttons.php', $data, false);
                        ?>
                    </div>
                    <div class="sectionbody">
                        <div class="tablewrap">
                            <div id="pageListing"> <?php echo Labels::getLabel('LBL_Pages', $adminLangId); ?></div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
