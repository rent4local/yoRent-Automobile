<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon">
                                <i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Manage_Addons', $adminLangId); ?> </h5>
                            <?php /* $this->includeTemplate('_partial/header/header-breadcrumb.php', array("removeHref"=>"addon-products")); */ ?>
                        </div>
                    </div>
                </div>
                <section class="section searchform_filter">
                    <div class="sectionhead">
                        <h4> <?php echo Labels::getLabel('LBL_Search...', $adminLangId); ?></h4>
                    </div>
                    <div class="sectionbody space togglewrap" style="display:none;">
                        <?php
                        $searchForm->setFormTagAttribute('id', 'frmSearchProductLisiting');
                        $searchForm->setFormTagAttribute('class', 'form web_form');
                        $searchForm->setFormTagAttribute('onsubmit', 'searchAddonProducts(this); return(false);');
                        $searchForm->getField('keyword')->addFieldTagAttribute('placeholder', Labels::getLabel('LBL_Search...', $adminLangId));
                        $searchForm->developerTags['colClassPrefix'] = 'col-md-';
                        $searchForm->developerTags['fld_default_col'] = 6;

                        $fldClear = $searchForm->getField('btn_clear');
                        $fldClear->setFieldTagAttribute('onclick', 'clearSearch()');
                        echo $searchForm->getFormHtml();
                        ?>
                    </div>
                </section>
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Addons_List', $adminLangId); ?> </h4>
                    </div>
                    <div class="sectionbody">
                        <div class="tablewrap">
                            <div id="addon-products-listing-js"></div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>