<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon">
                            <i class="ion-android-star"></i>
                            </span>
                            <h5><?php echo Labels::getLabel('LBL_Manage_Shops', $adminLangId); ?> </h5>
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
                            $frmSearch->setFormTagAttribute('onsubmit', 'searchShops(this); return(false);');
                            $frmSearch->setFormTagAttribute('class', 'web_form');
                            $frmSearch->developerTags['colClassPrefix'] = 'col-md-';
                            $frmSearch->developerTags['fld_default_col'] = 6;
                            $fld = $frmSearch->getField('btn_clear');
                            $fld->addFieldTagAttribute('onclick', 'clearShopSearch()');
                            echo  $frmSearch->getFormHtml();
                        ?>
                    </div>
                </section>
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Shop_List', $adminLangId); ?> </h4>
                        <?php
                            // $productType = Importexport::TYPE_PRODUCTS;

                            if ($canEdit) {
                                $this->includeTemplate('_partial/action-buttons.php', ['deleteButton' => false, 'adminLangId' => $adminLangId], false);
                            }
                        ?>
                    </div>
                    <div class="sectionbody">
                        <div class="tablewrap">
                            <div id="shopListing">
                                <?php echo Labels::getLabel('LBL_Processing...', $adminLangId); ?>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
