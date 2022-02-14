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
                            <h5><?php echo Labels::getLabel('LBL_Manage_Collections', $adminLangId); ?> </h5> <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <section class="section searchform_filter">
                    <div class="sectionhead">
                        <h4> <?php echo Labels::getLabel('LBL_Search...', $adminLangId); ?></h4>
                    </div>
                    <div class="sectionbody space togglewrap" style="display:none;">
                        <?php
                        $search->setFormTagAttribute('onsubmit', 'searchCollection(this); return(false);');
                        $search->setFormTagAttribute('class', 'web_form');
                        $search->setFormTagAttribute('id', 'frmSearch');
                        $search->developerTags['colClassPrefix'] = 'col-md-';
                        $search->developerTags['fld_default_col'] = 6;
                        $frmId = $search->getFormTagAttribute('id');
                        $fld = $search->getField('collection_type');
                        $fld->addFieldTagAttribute('onChange', 'getCollectionTypeLayout("' . $frmId . '",this.value,1); ');
                        $search->getField('keyword')->addFieldtagAttribute('class', 'search-input');
                        $search->getField('btn_clear')->addFieldtagAttribute('onclick', 'clearSearch();');

                        echo  $search->getFormHtml();
                        ?>
                    </div>
                </section>
                <div class="tabs_nav_container vertical">
                    <ul class="tabs_nav outerul vertical-actions">
                        <?php
						$layoutInstImgArr = Collections::getLayoutImagesArr();
						
                        foreach ($typeLayouts as $type => $layouts) {
							$index = 1;
							foreach ($layouts as $layoutType => $layout) { 
							if (!in_array($layoutType, $activeThemeLayouts)) {
								continue;
							}
							
							?>
                                <li>
                                    <span class="lable"><?php echo $layout. ' '. $index; ?></span>
                                    <div class="actions">
                                        <a href="javascript:void(0)" onclick="collectionForm(<?php echo $type; ?>, <?php echo $layoutType; ?>, 0)" title="<?php echo Labels::getLabel('LBL_Add_Collection', $adminLangId); ?>" class="btn-clean btn-sm btn-icon btn-secondary "><i class="fas fa-plus"></i></a>
                                        <a rel="facebox" onClick="displayImageInFacebox('<?php echo CONF_WEBROOT_FRONT_URL; ?>images/admin/collection_layouts/<?php echo $layoutInstImgArr[$layoutType]; ?>');" href="javascript:void(0)" title="<?php echo Labels::getLabel('LBL_Layout_Instruction', $adminLangId); ?>" class="btn-clean btn-sm btn-icon btn-secondary "><i class="fas fa-file-image"></i></a>
                                    </div>
                                </li>
                            <?php 
							$index++;
							} ?>
                        <?php } ?>
                    </ul>
                    <div id="frmBlock" class="tabs_panel_wrap">
                        <section>
                            <div class="d-flex justify-content-end">
                                <?php
                                $data = [
                                    'statusButtons' => $canEdit,
                                    'deleteButton' => $canEdit,
                                    'adminLangId' => $adminLangId
                                ];

                                /* if ($canEdit) {
                                    $data['otherButtons'][] = [
                                        'attr' => [
                                            'href' => 'javascript:void(0)',
                                            'onclick' => 'collectionForm(0)',
                                            'title' => Labels::getLabel('Lbl_Add_Collection', $adminLangId)
                                        ],
                                        'label' => '<i class="fas fa-plus"></i>'
                                    ];
                                } */

                                /* $data['otherButtons'][] = [
                                    'attr' => [
                                        'href' => 'javascript:void(0)',
                                        'onclick' => 'collectionLayouts()',
                                        'title' => Labels::getLabel('LBL_All_Layouts_Instructions', $adminLangId)
                                    ],
                                    'label' => '<i class="fas fa-file-image"></i>'
                                ]; */

                                $this->includeTemplate('_partial/action-buttons.php', $data, false);
                                ?>
                            </div>
                            <div class="sectionbody">
                                <div class="tablewrap">
                                    <div id="listing"> <?php echo Labels::getLabel('Lbl_Processing...', $adminLangId); ?>....</div>
                                </div>
                            </div>
                        </section>
                        <div class="tabs_panel"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>