<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon"><i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Manage_Order_Cancel_Rules', $adminLangId); ?> </h5> 
                            <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>


                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Order_Cancel_Rules_Listing', $adminLangId); ?></h4>
                        
                        <?php
                        if ($canEdit) {
                            $data = [
                                'adminLangId' => $adminLangId,
                                'statusButtons' => false,
                                'otherButtons' => [
                                    [
                                        'attr' => [
                                            'href' => 'javascript:void(0)',
                                            'onclick' => 'addEditRuleForm(0)',
                                            'title' => Labels::getLabel('LBL_Add_Rule', $adminLangId)
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
                    <?php
                        if(!empty($warningMsg)){
                            echo "<div class='cancle-order'><div class='row '><div class='col-md-auto'><h6 class='py-2'>".Labels::getLabel('LBL_Slots_Are_Missing_For_Following_Duartions', $adminLangId)."</h6></div><div class='col-md-8'><ul class='no-marker missing-duration'>";
                            foreach($warningMsg as $msg) {
                                echo '<li class="text-danger">'. $msg . '</li>';
                            }
                            echo "</ul></div></div></div>";
                        }
                        ?>
                        <div class="tablewrap">
                            <div id="listing"> <?php echo Labels::getLabel('LBL_Processing...', $adminLangId); ?></div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
