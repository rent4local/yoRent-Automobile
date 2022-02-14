<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?> <div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon"><i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Manage_Labels', $adminLangId); ?> </h5> <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <section class="section searchform_filter">
                    <div class="sectionhead">
                        <h4> <?php echo Labels::getLabel('LBL_Search', $adminLangId); ?></h4>
                    </div>
                    <div class="sectionbody space togglewrap" style="display:none;"> <?php
                        $frmSearch->setFormTagAttribute('onsubmit', 'searchLabels(this); return(false);');
                        $frmSearch->setFormTagAttribute('id', 'frmLabelsSearch');
                        $frmSearch->setFormTagAttribute('class', 'web_form');
                        $frmSearch->developerTags['colClassPrefix'] = 'col-md-';
                        $frmSearch->developerTags['fld_default_col'] = 4;

                        $btn = $frmSearch->getField('btn_clear');
                        $btn->setFieldTagAttribute('onClick', 'clearSearch()');
                        echo  $frmSearch->getFormHtml();
                        ?> </div>
                </section>
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Language_labels_List', $adminLangId); ?> </h4>
                        <?php
                        if ($canEdit) {
                            $data = [
                                'adminLangId' => $adminLangId,
                                'statusButtons' => false,
                                'deleteButton' => false,
                                'otherButtons' => [
                                    [
                                        'attr' => [
                                            'href' => 'javascript:void(0)',
                                            'onclick' => 'updateFile()',
                                            'title' => Labels::getLabel('LBL_UPDATE_WEB_LABEL_FILE', $adminLangId)
                                        ],
                                        'label' => '<i class="fas fa-laptop-code"></i>'
                                    ],
                                    /*[
                                        'attr' => [
                                            'href' => 'javascript:void(0)',
                                            'onclick' => "updateFile(" . Labels::TYPE_APP . ")",
                                            'title' => Labels::getLabel('LBL_UPDATE_APP_LABEL_FILE', $adminLangId)
                                        ],
                                        'label' => '<i class="fas fa-mobile-alt"></i>'
                                    ],*/
                                ]
                            ];
        
                            $this->includeTemplate('_partial/action-buttons.php', $data, false);
                        } ?>
                    </div>
                    <div class="sectionbody">
                        <div class="tablewrap">
                            <div id="listing"> <?php echo Labels::getLabel('LBL_processing...', $adminLangId); ?></div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
