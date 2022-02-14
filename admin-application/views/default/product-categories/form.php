<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
$prodCatFrm->setFormTagAttribute('class', 'web_form');
$prodCatFrm->setFormTagAttribute('id', 'frmProdCategory');
$prodCatFrm->setFormTagAttribute('onsubmit', 'setupCategory(); return(false);');

$activeFld = $prodCatFrm->getField('prodcat_active');

$catFld = $prodCatFrm->getField('parent_category_name');
$catFld->addFieldTagAttribute('onmousedown', 'clearTxt(this)');

$activeFld->setOptionListTagAttribute('class', 'list-inline-checkboxes');
$activeFld->developerTags['rdLabelAttributes'] = array('class' => 'radio');
$activeFld->developerTags['rdHtmlAfterRadio'] = '';

$compFld = $prodCatFrm->getField('prodcat_comparison');

$compFld->setOptionListTagAttribute('class', 'list-inline-checkboxes');
$compFld->developerTags['rdLabelAttributes'] = array('class' => 'radio');
$compFld->developerTags['rdHtmlAfterRadio'] = '';

$statusFld = $prodCatFrm->getField('prodcat_status');
if (null != $statusFld) {
    $statusFld->setOptionListTagAttribute('class', 'list-inline-checkboxes');
    $statusFld->developerTags['rdLabelAttributes'] = array('class' => 'radio');
    $statusFld->developerTags['rdHtmlAfterRadio'] = '';
}

$iconLangFld = $prodCatFrm->getField('icon_lang_id');
$iconLangFld->addFieldTagAttribute('class', 'icon-language-js');

$iconFld = $prodCatFrm->getField('cat_icon');
$iconFld->addFieldTagAttribute('class', 'btn btn-brand btn-sm');
$iconFld->addFieldTagAttribute('onChange', 'iconPopupImage(this)');
$iconFld->htmlAfterField = '<small class="text--small">' . sprintf(Labels::getLabel('LBL_This_will_be_displayed_in_%s_on_your_store', $adminLangId), $iconSizeArr['width']. 'x'. $iconSizeArr['height']) . '</small>';

$bannerFld = $prodCatFrm->getField('cat_banner');
$bannerFld->addFieldTagAttribute('class', 'btn btn-brand btn-sm');
$bannerFld->addFieldTagAttribute('onChange', 'bannerPopupImage(this)');
$bannerFld->htmlAfterField = '<small class="text--small" class="preferredDimensions-js">' . sprintf(Labels::getLabel('LBL_Preferred_Dimensions_%s', $adminLangId), $bannerSizeArr['width']. 'x'. $bannerSizeArr['height']) . '</small>';

$bannerLangFld = $prodCatFrm->getField('banner_lang_id');
$bannerLangFld->addFieldTagAttribute('class', 'banner-language-js');

$screenFld = $prodCatFrm->getField('slide_screen');
$screenFld->addFieldTagAttribute('class', 'prefDimensions-js');

$btn = $prodCatFrm->getField('btn_submit');
$btn->setFieldTagAttribute('class', "btn-clean btn-sm btn-icon btn-secondary");

$btn = $prodCatFrm->getField('btn_discard');
$btn->addFieldTagAttribute('onClick', "discardForm()");
$btn->setFieldTagAttribute('class', "btn-clean btn-sm btn-icon btn-secondary");

$fld = $prodCatFrm->getField('auto_update_other_langs_data');
if (null != $fld) {
    $fld->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
    $fld->developerTags['cbHtmlAfterCheckbox'] = '';
}
?>
<div class="page">
    <div class="container container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon">
                                <i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Category_Form', $adminLangId); ?> </h5>
                            <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class='container container-fluid'>
        <section class="section p-20">
            <div class="tabs_nav_container wizard-tabs-horizontal" style="margin-top:0px;">
                <ul class="tabs_nav">
                    <li>
                        <a class="active tabs_001" rel="tabs_001" href="javascript:void(0)">
                            <div class="tabs-head">
                                <div class="tabs-title"><?php echo Labels::getLabel('LBL_General', $adminLangId); ?><span><?php echo Labels::getLabel('LBL_General', $adminLangId); ?></span></div>
                            </div>
                        </a>
                    </li>
                    <?php if (FatApp::getConfig('CONF_USE_CUSTOM_FIELDS', FatUtility::VAR_INT, 0) == applicationConstants::YES) { ?>
                        <li>
                            <a <?php if ($prodCatId > 0) { ?> rel="tabs_002" class="tabs_002" <?php } else { ?> style="pointer-events: none;" <?php } ?> href="javascript:void(0)">
                                <div class="tabs-head">
                                    <div class="tabs-title"><?php echo Labels::getLabel('LBL_Custom_fields', $adminLangId); ?> <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="<?php echo Labels::getLabel('LBL_Custom_fields_tooltip_text', $adminLangId); ?>"></i><span><?php echo Labels::getLabel('LBL_Add_custom_fields', $adminLangId); ?></span></div>
                                </div>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
                <!-- TAB SECTION START HERE -->
                <div class="tabs_panel_wrap">
                    <div id="tabs_001" class="tabs_panel" style="display: block;">
                        <?php echo $prodCatFrm->getFormTag(); ?>
                        <div class="sectionbody space">
                            <div class="row justify-content-center">
                                <div class="col-md-12">
                                    <h3 class="form__heading"><?php echo Labels::getLabel('LBL_General', $adminLangId); ?></h3>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="field-set">
                                                <div class="caption-wraper">
                                                    <label class="field_label">
                                                        <?php
                                                        $fld = $prodCatFrm->getField('prodcat_identifier');
                                                        echo $fld->getCaption();
                                                        ?>
                                                        <span class="spn_must_field">*</span>
                                                    </label>
                                                </div>
                                                <div class="field-wraper">
                                                    <div class="field_cover">
                                                        <?php echo $prodCatFrm->getFieldHtml('prodcat_identifier'); ?>
                                                        <?php echo $prodCatFrm->getFieldHtml('prodcat_id'); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-set">
                                                <div class="caption-wraper">
                                                    <label class="field_label">
                                                        <?php
                                                        $fld = $prodCatFrm->getField('prodcat_name[' . $siteDefaultLangId . ']');
                                                        echo $fld->getCaption();
                                                        ?>
                                                        <span class="spn_must_field">*</span></label>
                                                </div>
                                                <div class="field-wraper">
                                                    <div class="field_cover">
                                                        <?php echo $prodCatFrm->getFieldHtml('prodcat_name[' . $siteDefaultLangId . ']'); ?>
                                                        <?php echo $prodCatFrm->getFieldHtml('prodcat_id'); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="field-set">
                                                <div class="caption-wraper">
                                                    <label class="field_label">
                                                        <?php
                                                        $fld = $prodCatFrm->getField('parent_category_name');
                                                        echo $fld->getCaption();
                                                        ?></label>
                                                </div>
                                                <div class="field-wraper">
                                                    <div class="field_cover">
                                                        <?php echo $prodCatFrm->getFieldHtml('parent_category_name'); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-set">
                                                <div class="caption-wraper">
                                                    <label class="field_label">
                                                        <?php
                                                        $fld = $prodCatFrm->getField('prodcat_active');
                                                        echo $fld->getCaption();
                                                        ?></label>
                                                </div>
                                                <div class="field-wraper">
                                                    <div class="field_cover">
                                                        <?php echo $prodCatFrm->getFieldHtml('prodcat_active'); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="field-set">
                                                <div class="caption-wraper">
                                                    <label class="field_label">
                                                        <?php
                                                        $fld = $prodCatFrm->getField('prodcat_comparison');
                                                        echo $fld->getCaption();
                                                        ?></label>
                                                </div>
                                                <div class="field-wraper">
                                                    <div class="field_cover">
                                                        <?php echo $prodCatFrm->getFieldHtml('prodcat_comparison'); ?>
                                                    </div>
                                                </div>
                                            </div>
											</div>
											<?php
											$translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
											if (!empty($translatorSubscriptionKey) && count($otherLangData) > 0) {
											?>
                                        
                                            <div class="col-md-6">
                                                <div class="field-set">
                                                    <?php echo $prodCatFrm->getFieldHtml('auto_update_other_langs_data'); ?>
                                                </div>
                                            </div>
                                        
											<?php } ?>
											
                                        
                                        <?php if (null != $statusFld) { ?>
                                            <div class="col-md-12">
                                                <div class="field-set d-flex align-items-center">
                                                    <div class="caption-wraper w-auto pr-4">
                                                        <label class="field_label">
                                                            <?php echo $statusFld->getCaption(); ?>
                                                        </label>
                                                    </div>
                                                    <div class="field-wraper w-auto">
                                                        <div class="field_cover">
                                                            <?php echo $prodCatFrm->getFieldHtml('prodcat_status'); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12">
                                        <?php    
                                        /* [ MEDIA INSTRUCTIONS START HERE */ 
                                        $tpl = new FatTemplate('', '');
                                        $tpl->set('adminLangId', $adminLangId);
                                        echo $tpl->render(false, false, '_partial/imageUploadInstructions.php', true, true);
                                        /* ] */    
                                        ?>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                        <div class="p-4 mb-4 border rounded">
                                        <h3 class="mb-4"><?php echo Labels::getLabel('LBL_Banner', $adminLangId); ?></h3>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="field-set">
                                                    <div class="caption-wraper"><label class="field_label">
                                                            <?php
                                                            $fld = $prodCatFrm->getField('banner_lang_id');
                                                            echo $fld->getCaption();
                                                            ?>
                                                        </label></div>
                                                    <div class="field-wraper">
                                                        <div class="field_cover">
                                                            <?php echo $prodCatFrm->getFieldHtml('banner_lang_id'); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="field-set">
                                                    <div class="caption-wraper"><label class="field_label">
                                                            <?php
                                                            $fld = $prodCatFrm->getField('slide_screen');
                                                            echo $fld->getCaption();
                                                            ?>
                                                        </label></div>
                                                    <div class="field-wraper">
                                                        <div class="field_cover screen-type-banner--js" data-type="banner">
                                                            <?php echo $prodCatFrm->getFieldHtml('slide_screen'); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="field-set">
                                                    <div class="caption-wraper"><label class="field_label">
                                                        </label></div>
                                                    <div class="field-wraper">
                                                        <div class="field_cover">
                                                            <?php
                                                            echo $prodCatFrm->getFieldHtml('banner_file_type');
                                                            echo $prodCatFrm->getFieldHtml('cat_banner');
                                                            ?>
                                                            <?php
                                                            foreach ($mediaLanguages as $key => $data) {
                                                                foreach ($screenArr as $key1 => $screen) {
                                                                    echo $prodCatFrm->getFieldHtml('cat_banner_image_id[' . $key . '_' . $key1 . ']');
                                                                }
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4" id="banner-image-listing" style="min-height: 136px;"></div>
                                        </div>
                                    </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                        <div class="p-4 mb-4 border rounded">
                                        <h3 class="mb-4"><?php echo Labels::getLabel('LBL_Icon', $adminLangId); ?></h3>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="field-set">
                                                    <div class="caption-wraper"><label class="field_label">
                                                            <?php
                                                            $fld = $prodCatFrm->getField('icon_lang_id');
                                                            echo $fld->getCaption();
                                                            ?>
                                                        </label></div>
                                                    <div class="field-wraper">
                                                        <div class="field_cover">
                                                            <?php echo $prodCatFrm->getFieldHtml('icon_lang_id'); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="field-set">
                                                    <div class="caption-wraper"><label class="field_label">
                                                        </label></div>
                                                    <div class="field-wraper">
                                                        <div class="field_cover">
                                                            <?php
                                                            echo $prodCatFrm->getFieldHtml('icon_file_type');
                                                            echo $prodCatFrm->getFieldHtml('cat_icon');
                                                            ?>
                                                            <?php
                                                            foreach ($mediaLanguages as $key => $data) {
                                                                echo $prodCatFrm->getFieldHtml('cat_icon_image_id[' . $key . ']');
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4" id="icon-image-listing" style="min-height: 136px;"></div>
                                        </div>
                                    </div>
                                    </div>
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
                                                                            $fld = $prodCatFrm->getField('prodcat_name[' . $langId . ']');
                                                                            echo $fld->getCaption();
                                                                            ?>
                                                                        </label>
                                                                    </div>
                                                                    <div class="field-wraper">
                                                                        <div class="field_cover">
                                                                            <?php echo $prodCatFrm->getFieldHtml('prodcat_name[' . $langId . ']'); ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                    <?php
                                        }
                                    }
                                    ?>
                                    <?php if (0 < $productReq) { ?>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <?php echo $prodCatFrm->getFieldHtml('btn_submit'); ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            
                            <?php if (1 > $productReq) { ?>
                            <div class="row justify-content-center">
                                <div class="col-md-12 text--right">
                                    <h4></h4>
                                    <div class="section__toolbar">
                                        <?php echo $prodCatFrm->getFieldHtml('btn_discard'); ?>
                                        <?php echo $prodCatFrm->getFieldHtml('btn_submit'); ?>
                                    </div>
                                </div>
                            </div>

                        <?php } ?>
                            
                            
                        </div>
                        <?php
                        echo $prodCatFrm->getFieldHtml('banner_min_width');
                        echo $prodCatFrm->getFieldHtml('banner_min_height');
                        echo $prodCatFrm->getFieldHtml('logo_min_width');
                        echo $prodCatFrm->getFieldHtml('logo_min_height');
                        echo $prodCatFrm->getFieldHtml('prodcat_parent');
                        ?>
                        </form>
                        <?php
                        echo $prodCatFrm->getExternalJS();

                        $catAutocompleteArr = [];
                        foreach ($categories as $catId => $catName) {
                            $catAutocompleteArr[] = array(
                                'id' => $catId,
                                'label' => strip_tags(html_entity_decode($catName, ENT_QUOTES, 'UTF-8'))
                            );
                        }
                        ?>
                    </div>

                    <div id="tabs_002" class="tabs_panel" style="display: none;">
                        <div class="row">
                            <div class="col-md-7 mb-3 mb-md-0">
                                <h3 class="form__heading"><?php echo Labels::getLabel('LBL_Custom_fields', $adminLangId); ?></h3>
                                <div id="custom-fields-listing-js"></div>
                            </div>
                            <div class="col-md-1 mb-0 mb-md-0"><span class="hz-line"></span></div>
                            <div class="col-md-4 mb-3 mb-md-0">
                                <h3 class="form__heading"><?php echo Labels::getLabel('LBL_Custom_fields_form', $adminLangId); ?></h3>
                                <div id="custom-fields-form-js"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- TAB SECTION END HERE -->
            </div>
        </section>
    </div>
</div>
<script>
    $('input[name=banner_min_width]').val('<?php echo $bannerSizeArr['width']?>');
    $('input[name=banner_min_height]').val('<?php echo $bannerSizeArr['height']?>');
    $('input[name=logo_min_width]').val('<?php echo $iconSizeArr['width']?>');
    $('input[name=logo_min_height]').val('<?php echo $iconSizeArr['height']?>');
   
    $(document).ready(function() {
        var catAutocompleteArr = <?php echo json_encode($catAutocompleteArr);  ?>;
        $('input[name=\'parent_category_name\']').autocomplete({
            minLength: 0,
            'classes': {
                "ui-autocomplete": "custom-ui-autocomplete"
            },
            source: catAutocompleteArr,
            select: function(event, ui) {
                $('input[name=\'prodcat_parent\']').val(ui.item.id);
            }
        }).focus(function() {
            $(this).autocomplete('search', $(this).val())
        });

        $('input[name=\'parent_category_name\']').change(function() {
            if ($(this).val() == '') {
                $("input[name='prodcat_parent']").val(0);
            }
        });
        
    });

    function clearTxt(obj) {
        $(obj).val('');
    }
    
</script>
<?php if ($prodCatId > 0) { ?>
    <script>
        $(document).ready(function() {
            categoryImages(<?php echo $prodCatId; ?>, 'icon', 1, 0);
            categoryImages(<?php echo $prodCatId; ?>, 'banner', 1, 0)
        });
    </script>
<?php } ?>