<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form layout--' . $formLayout);
$frm->setFormTagAttribute('onsubmit', 'setupCollection(); return(false);');

if (!in_array($collection_layout_type, Collections::APP_COLLECTIONS_ONLY)) {
    $fld = $frm->getField('collection_for_web');
    $fld->setOptionListTagAttribute('class', 'list-inline-checkboxes');
    $fld->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
    $fld->developerTags['cbHtmlAfterCheckbox'] = '';
}

$fld = $frm->getField('collection_for_app');
$fld->setOptionListTagAttribute('class', 'list-inline-checkboxes');
$fld->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
$fld->developerTags['cbHtmlAfterCheckbox'] = '';
if (in_array($collection_layout_type, Collections::APP_COLLECTIONS_ONLY)) {
    $fld->setFieldTagAttribute('disabled', 'disabled');
}

$fld = $frm->getField('auto_update_other_langs_data');
if (null != $fld) {
    $fld->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
    $fld->developerTags['cbHtmlAfterCheckbox'] = '';
}
$siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
?>
<section class="section">
    <div class="sectionbody space">
        <div class="row">
            <div class="col-sm-12">
                <div class="tabs_nav_container responsive flat">
                    <?php if ($collection_layout_type != Collections::TYPE_PENDING_REVIEWS1) { ?>
                        <ul class="tabs_nav">
                            <li><a class="active" href="javascript:void(0)" onclick="collectionForm(<?php echo $collection_type ?>, <?php echo $collection_layout_type ?>, <?php echo $collection_id ?>, 0);">
                                    <?php echo Labels::getLabel('LBL_General', $adminLangId); ?></a>
                            </li>
                            <?php $inactive = ($collection_id == 0) ? 'fat-inactive' : ''; ?>
                            <?php if (!in_array($collection_type, Collections::COLLECTION_WITHOUT_RECORDS) && $collection_layout_type != Collections::TYPE_PRODUCT_LAYOUT5 && $collection_layout_type != Collections::TYPE_CATEGORY_LAYOUT4) { ?>
                                <li><a class="<?php echo $inactive; ?>" href="javascript:void(0)" <?php if ($collection_id > 0) { ?> onclick="recordForm(<?php echo $collection_id ?>, <?php echo $collection_type ?>);" <?php } ?>>
									<?php echo Labels::getLabel('LBL_Link_Records', $adminLangId); ?></a>
                                </li>
							<?php } ?>
							
							<?php if($collection_type == Collections::COLLECTION_TYPE_CONTENT_BLOCK_WITH_ICON) { 
								for ($recordIndex = 1; $recordIndex <= $recordLimit; $recordIndex++) { ?>
								<li>
									<a class="<?php echo $inactive; ?>" href="javascript:void(0)" <?php if ($collection_id > 0) { ?> onclick="addRecordForm(<?php echo $collection_id ?>, <?php echo $collection_type ?>, <?php echo $recordIndex;?>);" <?php } ?>>
									<?php echo Labels::getLabel('LBL_Step_'. $recordIndex, $adminLangId); ?></a>
                                </li>
							<?php } 
							} ?>
							

                            <?php
                            if ($collection_layout_type == Collections::TYPE_PRODUCT_LAYOUT5) {
                                if (!empty($subCollectionsList)) {
                                    foreach ($subCollectionsList as $subCollection) {
                                        ?>
                                        <li>
                                            <a class="<?php echo $inactive; ?>" href="javascript:void(0)" <?php if ($collection_id > 0) { ?> onclick="tabsForm(<?php echo $collection_id ?>, <?php echo $subCollection['collection_id']; ?>, <?php echo Collections::COLLECTION_TYPE_SUB_COLLECTION; ?>);" <?php } ?>>
                                                <?php echo $subCollection['collection_name']; ?>
                                            </a>
                                        </li>
                                        <?php
                                    }
                                }
                                ?>
                                <?php if (count($subCollectionsList) < $recordLimit) { ?>
                                    <li>
                                        <a class="<?php echo $inactive; ?>" href="javascript:void(0)"  onclick="tabsForm(<?php echo $collection_id ?>, 0);">
                                            <?php echo Labels::getLabel('LBL_Tab_+', $adminLangId); ?>
                                        </a>
                                    </li>
                                <?php } ?>
                            <?php } ?>

                            <?php if ($collection_type == Collections::COLLECTION_TYPE_BANNER) { ?>
                                <li><a class="<?php echo $inactive; ?>" href="javascript:void(0)" <?php if ($collection_id > 0) { ?> onclick="banners(<?php echo $collection_id ?>);" <?php } ?>>
                                        <?php echo Labels::getLabel('LBL_Banners', $adminLangId); ?></a>
                                </li>
                            <?php } ?>

                            <?php if (in_array($collection_layout_type, Collections::LAYOUT_WITH_MEDIA)) { ?>
                                <li>
                                    <a class="<?php echo $inactive; ?>" href="javascript:void(0)" <?php if ($collection_id > 0) { ?> onclick="collectionMediaForm(<?php echo $collection_id ?>);" <?php } ?>>
                                        <?php echo Labels::getLabel('LBL_Media', $adminLangId); ?>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    <?php } ?>
                    <div class="tabs_panel_wrap">
                        <div class="tabs_panel" id="tabs_form">
                            <?php echo $frm->getFormTag(); ?>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="field-set">
                                        <div class="caption-wraper">
                                            <label class="field_label">
                                                <?php
                                                $fld = $frm->getField('collection_name[' . $siteDefaultLangId . ']');
                                                echo $fld->getCaption();
                                                ?>
                                                <span class="spn_must_field">*</span></label>
                                        </div>
                                        <div class="field-wraper">
                                            <div class="field_cover">
                                                <?php echo $frm->getFieldHtml('collection_name[' . $siteDefaultLangId . ']'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-8">
                                    <div class="field-set">
                                        <div class="caption-wraper">
                                            <label class="field_label">
                                                <?php
                                                $fld = $frm->getField('collection_description[' . $siteDefaultLangId . ']');
                                                echo $fld->getCaption();
                                                ?>
											</label>
                                        </div>
                                        <div class="field-wraper">
                                            <div class="field_cover">
                                                <?php echo $frm->getFieldHtml('collection_description[' . $siteDefaultLangId . ']'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php if (applicationConstants::getActiveTheme() == applicationConstants::THEME_FASHION && $collection_layout_type == Collections::TYPE_PRODUCT_LAYOUT5) { ?>
                                    <div class="col-md-12">
                                        <div class="field-set">
                                            <div class="caption-wraper">
                                                <label class="field_label">
                                                    <?php
                                                    $fld = $frm->getField('collection_text[' . $siteDefaultLangId . ']');
                                                    echo $fld->getCaption();
                                                    ?>
                                                </label>
                                            </div>
                                            <div class="field-wraper">
                                                <div class="field_cover">
                                                    <?php echo $frm->getFieldHtml('collection_text[' . $siteDefaultLangId . ']'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="field-set">
                                            <div class="caption-wraper">
                                                <label class="field_label">
                                                    <?php
                                                    $fld = $frm->getField('collection_link_url');
                                                    echo $fld->getCaption();
                                                    ?>
                                                </label>
                                            </div>
                                            <div class="field-wraper">
                                                <div class="field_cover">
                                                    <?php echo $frm->getFieldHtml('collection_link_url'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>


                                <?php if ($collection_type == Collections::COLLECTION_TYPE_CONTENT_BLOCK) { ?>
                                    <div class="col-md-12">
                                        <div class="field-set">
                                            <div class="caption-wraper">
                                                <label class="field_label">
                                                    <?php
                                                    $fld = $frm->getField('epage_content_' . $siteDefaultLangId);
                                                    echo $fld->getCaption();
                                                    ?>
                                                    <span class="spn_must_field">*</span></label>
                                            </div>
                                            <div class="field-wraper">
                                                <div class="field_cover">
                                                    <?php echo $frm->getFieldHtml('epage_content_' . $siteDefaultLangId); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                    echo $frm->getFieldHtml('epage_id');
                                }
                                ?>
                                <?php if ($collection_type == Collections::COLLECTION_TYPE_BANNER) { ?>
                                    <div class="col-md-6">
                                        <div class="field-set">
                                            <div class="caption-wraper">
                                                <label class="field_label">
                                                    <?php
                                                    $fld = $frm->getField('blocation_promotion_cost');
                                                    echo $fld->getCaption();
                                                    ?>
                                                </label>
                                            </div>
                                            <div class="field-wraper">
                                                <div class="field_cover">
                                                    <?php echo $frm->getFieldHtml('blocation_promotion_cost'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($collection_layout_type == Collections::TYPE_BANNER_LAYOUT4) { ?>
                                    <div class="col-md-6">
                                        <div class="field-set">
                                            <div class="caption-wraper">
                                                <label class="field_label">
                                                    <?php
                                                    $fld = $frm->getField('blocation_promotion_cost_second');
                                                    echo $fld->getCaption();
                                                    ?>
                                                </label>
                                            </div>
                                            <div class="field-wraper">
                                                <div class="field_cover">
                                                    <?php echo $frm->getFieldHtml('blocation_promotion_cost_second'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php } ?>
                                <?php } ?>
                            </div>
                            <div class="row">
                                <?php if (!in_array($collection_layout_type, Collections::APP_COLLECTIONS_ONLY)) { ?>
                                    <div class="col-md-4 d-none">
                                        <div class="field-set d-flex align-items-center">
                                            <div class="field-wraper w-auto">
                                                <div class="field_cover">
                                                    <?php echo $frm->getFieldHtml('collection_for_web'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                                <div class="col-md-4 d-none">
                                    <div class="field-set d-flex align-items-center">
                                        <div class="field-wraper w-auto">
                                            <div class="field_cover">
                                                <?php echo $frm->getFieldHtml('collection_for_app'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
                                if (!empty($translatorSubscriptionKey) && count($otherLangData) > 0) {
                                    ?>
                                    <div class="col-md-4">
                                        <div class="field-set d-flex align-items-center">
                                            <div class="field-wraper w-auto">
                                                <div class="field_cover">
                                                    <?php echo $frm->getFieldHtml('auto_update_other_langs_data'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>

                            <?php
                            if (!empty($otherLangData)) {
                                foreach ($otherLangData as $langId => $data) {
                                    ?>
                                    <div class="accordians_container accordians_container-categories" defaultLang="<?php echo $siteDefaultLangId; ?>" language="<?php echo $langId; ?>" id="accordion-language_<?php echo $langId; ?>" onClick="translateData(this)">
                                        <div class="accordian_panel">
                                            <span class="accordian_title accordianhead accordian_title" id="collapse_<?php echo $langId; ?>">
                                                <?php
                                                echo $data . " ";
                                                echo Labels::getLabel('LBL_Language_Data', $adminLangId);
                                                ?>
                                            </span>
                                            <div class="accordian_body accordiancontent" style="display: none;">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="field-set">
                                                            <div class="caption-wraper">
                                                                <label class="field_label">
                                                                    <?php
                                                                    $fld = $frm->getField('collection_name[' . $langId . ']');
                                                                    echo $fld->getCaption();
                                                                    ?>
                                                                </label>
                                                            </div>
                                                            <div class="field-wraper">
                                                                <div class="field_cover">
                                                                    <?php echo $frm->getFieldHtml('collection_name[' . $langId . ']'); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="field-set">
                                                            <div class="caption-wraper">
                                                                <label class="field_label">
                                                                    <?php
                                                                    $fld = $frm->getField('collection_description[' . $langId . ']');
                                                                    echo $fld->getCaption();
                                                                    ?>
                                                                </label>
                                                            </div>
                                                            <div class="field-wraper">
                                                                <div class="field_cover">
                                                                    <?php echo $frm->getFieldHtml('collection_description[' . $langId . ']'); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php if (applicationConstants::getActiveTheme() == applicationConstants::THEME_FASHION  && $collection_layout_type == Collections::TYPE_PRODUCT_LAYOUT5) { ?>
                                                        <div class="col-md-12">
                                                            <div class="field-set">
                                                                <div class="caption-wraper">
                                                                    <label class="field_label">
                                                                        <?php
                                                                        $fld = $frm->getField('collection_text[' . $langId . ']');
                                                                        echo $fld->getCaption();
                                                                        ?>
                                                                        <span class="spn_must_field">*</span></label>
                                                                </div>
                                                                <div class="field-wraper">
                                                                    <div class="field_cover">
                                                                        <?php echo $frm->getFieldHtml('collection_text[' . $langId . ']'); ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                                <?php if ($collection_type == Collections::COLLECTION_TYPE_CONTENT_BLOCK) { ?>
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="field-set">
                                                                <div class="caption-wraper">
                                                                    <label class="field_label">
                                                                        <?php
                                                                        $fld = $frm->getField('epage_content_' . $langId);
                                                                        echo $fld->getCaption();
                                                                        ?>
                                                                    </label>
                                                                </div>
                                                                <div class="field-wraper">
                                                                    <div class="field_cover">
                                                                        <?php echo $frm->getFieldHtml('epage_content_' . $langId); ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="field-set d-flex align-items-center">
                                        <div class="field-wraper w-auto">
                                            <div class="field_cover">
                                                <?php echo $frm->getFieldHtml('btn_submit'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                            echo $frm->getFieldHtml('collection_id');
                            echo $frm->getFieldHtml('collection_active');
                            echo $frm->getFieldHtml('collection_type');
                            echo $frm->getFieldHtml('collection_layout_type');
                            echo $frm->getFieldHtml('collection_parent_id');
                            ?>
                            </form>
                            <?php echo $frm->getExternalJS(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script type="text/javascript">
    $(document).ready(function () {
        callCollectionTypePopulate
                (<?php echo $collection_type; ?>);
    });
</script>