<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$frmSearch->setFormTagAttribute('class', 'web_form last_td_nowrap');
$frmSearch->setFormTagAttribute('onsubmit', 'searchDurationDiscounts(this); return(false);');
$frmSearch->developerTags['colClassPrefix'] = 'col-md-';
$frmSearch->developerTags['fld_default_col'] = 4;
$fld_active = $frmSearch->getField('active');
$cancelBtnFld = $frmSearch->getField('btn_clear');
$cancelBtnFld->setFieldTagAttribute('onclick', 'clearSearch();');
?>
<div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon"><i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Manage_Duration_Discount', $adminLangId); ?> </h5>
                            <small class="note">(<?php echo Labels::getLabel('LBL_Valid_Only_With_Rental_Products', $adminLangId); ?>)</small>
                            <?php /* $this->includeTemplate('_partial/header/header-breadcrumb.php'); */ ?>
                        </div>
                    </div>
                </div>
                <section class="section searchform_filter">
                    <div class="sectionhead">
                        <h4> <?php echo Labels::getLabel('LBL_Search...', $adminLangId); ?></h4>
                    </div>
                    <div class="sectionbody space togglewrap" style="display:none;">
                        <?php echo $frmSearch->getFormHtml(); ?>
                    </div>
                </section>
                <!--<div class="col-sm-12">-->
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Duration_Discount_Listing', $adminLangId); ?> </h4>
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
                                            'onclick' => 'deleteSpecialPriceRows()',
                                            'title' => Labels::getLabel('LBL_Delete_Special_Price', $adminLangId),
                                            'class' => 'd-none toolbar-btn-js'
                                        ],
                                        'label' => '<i class="fas fa-trash"></i>'
                                    ],
                                ]
                            ];

                            $this->includeTemplate('_partial/action-buttons.php', $data, false);
                        }
                        ?>
                    </div>
                    <div class="sectionbody">
                        <div class="tablewrap">
                            <div id="discount-form--js"></div>
                            <div id="listing"> <?php echo Labels::getLabel('LBL_Processing...', $adminLangId); ?></div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>