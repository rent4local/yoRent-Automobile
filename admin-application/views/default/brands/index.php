<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon"><i class="ion-android-star"></i></span>
                            <h5>
                                <?php echo Labels::getLabel('LBL_Manage_Product_Brands', $adminLangId); ?>
                                <?php echo (isset($prodBrandData['brand_identifier']))?$prodBrandData['brand_identifier']:'';?>
                            </h5>
                            <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <!---<div class="col-sm-12">-->
                <section class="section searchform_filter">
                    <div class="sectionhead">
                        <h4> <?php echo Labels::getLabel('LBL_Search...', $adminLangId); ?> </h4>
                    </div>
                    <div class="sectionbody space togglewrap" style="display:none;">
                        <?php
                            $search->setFormTagAttribute('onsubmit', 'searchProductBrands(this); return(false);');
                            $search->setFormTagAttribute('class', 'web_form');
                            $search->developerTags['colClassPrefix'] = 'col-md-';
                            $search->developerTags['fld_default_col'] = 6;
                            echo  $search->getFormHtml();
                        ?>
                    </div>
                </section>
                <!--<div class="col-sm-12"> -->
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Brands_List', $adminLangId); ?> </h4>
                        <?php 
                        if ($canEdit) {
                            $data = [
                                'adminLangId' => $adminLangId,
                                'otherButtons' => [
                                    [
                                        'attr' => [
                                            'href' => 'javascript:void(0)',
                                            'onclick' => 'addBrandForm(0)',
                                            'title' => Labels::getLabel('LBL_Add_Brand', $adminLangId),
                                            'class' => 'default-action--js'
                                        ],
                                        'label' => '<i class="fas fa-plus"></i>'
                                    ],
                                    [
                                        'attr' => [
                                            'href' => 'javascript:void(0)',
                                            'onclick' => 'deletedBrands()',
                                            'title' => Labels::getLabel('LBL_Deleted_Brands', $adminLangId),
                                            'class' => 'default-action--js'
                                        ],
                                        'label' => '<i class="fas fa-stream"></i>'
                                    ],
                                ]
                            ];
                            $this->includeTemplate('_partial/action-buttons.php', $data, false);
                        }
                        ?>
                    </div>
                    <div class="sectionbody">
                        <div class="tablewrap" >
                            <div id="listing"> <?php echo Labels::getLabel('LBL_Processing...', $adminLangId); ?></div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
