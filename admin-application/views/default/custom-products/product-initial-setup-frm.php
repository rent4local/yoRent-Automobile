<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$customProductFrm->setFormTagAttribute('class', 'web_form mt-5');
$customProductFrm->setFormTagAttribute('onsubmit', 'setupProduct(this); return(false);');

/* $customProductFrm->developerTags['colClassPrefix'] = 'col-md-';
$customProductFrm->developerTags['fld_default_col'] = 12;
$brandFld = $customProductFrm->getField('brand_name');
$brandFld->setWrapperAttribute('class', 'ui-front'); */
/* $optionFld = $customProductFrm->getField('option_name');
$optionFld->setWrapperAttribute('class', 'ui-front');
$tagFld = $customProductFrm->getField('tag_name');
$tagFld->setWrapperAttribute('class', 'ui-front');

$productCodEnabledFld = $customProductFrm->getField('product_cod_enabled');
$productCodEnabledFld->setWrapperAttribute('class', 'product_cod_enabled_fld'); */

$allowSale = FatApp::getConfig("CONF_ALLOW_SALE", FatUtility::VAR_INT, 0);
?>
<div class="row justify-content-center">
    <div class="col-md-12">
        <?php echo $customProductFrm->getFormTag(); ?>
        <div class="row">
            <div class="col-md-6">
                <div class="field-set">
                    <div class="caption-wraper">
                        <label class="field_label">
                            <?php
                            $fld = $customProductFrm->getField('product_identifier');
                            echo $fld->getCaption();
                            ?>
                        </label>
                        <span class="spn_must_field">*</span>
                    </div>
                    <div class="field-wraper">
                        <div class="field_cover">
                            <?php echo $customProductFrm->getFieldHtml('product_identifier'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="field-set">
                    <div class="caption-wraper">
                        <label class="field_label">
                            <?php
                            $fld = $customProductFrm->getField('brand_name');
                            echo $fld->getCaption();
                            ?>
                        </label>
                        <?php if (FatApp::getConfig("CONF_PRODUCT_BRAND_MANDATORY", FatUtility::VAR_INT, 1)) { ?>
                            <span class="spn_must_field">*</span>
                        <?php } ?>
                    </div>
                    <div class="field-wraper">
                        <div class="field_cover">
                            <?php echo $customProductFrm->getFieldHtml('brand_name'); ?>
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
                            $fld = $customProductFrm->getField('category_name');
                            echo $fld->getCaption();
                            ?>
                        </label>
                        <span class="spn_must_field">*</span>
                    </div>
                    <div class="field-wraper">
                        <div class="field_cover">
                            <?php echo $customProductFrm->getFieldHtml('category_name'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php if($allowSale) { ?>
                <div class="col-md-6">
                    <div class="field-set">
                        <div class="caption-wraper">
                            <label class="field_label">
                                <?php
                                $fld = $customProductFrm->getField('taxcat_name');
                                echo $fld->getCaption();
                                ?>
                            </label>
                            <span class="spn_must_field">*</span>
                        </div>
                        <div class="field-wraper">
                            <div class="field_cover">
                                <?php echo $customProductFrm->getFieldHtml('taxcat_name'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
            
            <div class="col-md-6">
                <div class="field-set">
                    <div class="caption-wraper">
                        <label class="field_label">
                            <?php
                            $fld = $customProductFrm->getField('taxcat_name_rent');
                            echo $fld->getCaption();
                            ?>
                        </label>
                        <span class="spn_must_field">*</span>
                    </div>
                    <div class="field-wraper">
                        <div class="field_cover">
                            <?php echo $customProductFrm->getFieldHtml('taxcat_name_rent'); ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php if($allowSale) { ?>
                <div class="col-md-6">
                    <div class="field-set">
                        <div class="caption-wraper">
                            <label class="field_label">
                                <?php
                                $fld = $customProductFrm->getField('product_min_selling_price');
                                echo $fld->getCaption();
                                ?>
                            </label>
                            <span class="spn_must_field">*</span>
                        </div>
                        <div class="field-wraper">
                            <div class="field_cover">
                                <?php echo $customProductFrm->getFieldHtml('product_min_selling_price'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="field-set">
                    <div class="caption-wraper">
                        <label class="field_label">
                            <?php
                            $fld = $customProductFrm->getField('product_active');
                            echo $fld->getCaption();
                            ?>
                        </label>
                    </div>
                    <div class="field-wraper">
                        <div class="field_cover">
                            <?php echo $customProductFrm->getFieldHtml('product_active'); ?>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
		<?php /* 
        <div class="row">
            <div class="col-md-6">
                <div class="field-set">
                    <div class="caption-wraper"></div>
                    <div class="field-wraper">
                        <div class="field_cover">
                            <?php echo $customProductFrm->getFieldHtml('product_enable_rfq'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div> */ ?>
        <?php $divLayout = Language::getLayoutDirection($siteDefaultLangId); ?>
        <div class="p-4 mb-4 bg-gray rounded layout--<?php echo $divLayout; ?>">
            <div class="row">
                <div class="col-md-6">
                    <div class="field-set">
                        <div class="caption-wraper">
                            <label class="field_label">
                                <?php
                                $fld = $customProductFrm->getField('product_name[' . $siteDefaultLangId . ']');
                                echo $fld->getCaption();
                                ?>
                                <span class="spn_must_field">*</span>
                            </label>
                        </div>
                        <div class="field-wraper">
                            <div class="field_cover">
                                <?php echo $customProductFrm->getFieldHtml('product_name[' . $siteDefaultLangId . ']'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="field-set">
                        <div class="caption-wraper">
                            <label class="field_label">
                                <?php
                                $fld = $customProductFrm->getField('product_youtube_video[' . $siteDefaultLangId . ']');
                                echo $fld->getCaption();
                                ?>
                            </label>
                        </div>
                        <div class="field-wraper">
                            <div class="field_cover">
                                <?php echo $customProductFrm->getFieldHtml('product_youtube_video[' . $siteDefaultLangId . ']'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="field-set mb-0">
                        <div class="caption-wraper">
                            <label class="field_label">
                                <?php
                                $fld = $customProductFrm->getField('product_description_' . $siteDefaultLangId);
                                echo $fld->getCaption();
                                ?>
                            </label>
                        </div>
                        <div class="field-wraper">
                            <div class="field_cover"> 
                                <?php echo $customProductFrm->getFieldHtml('product_description_' . $siteDefaultLangId); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
            if (!empty($translatorSubscriptionKey) && count($otherLanguages) > 0) {
                ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="field-set mb-0">
                            <div class="caption-wraper"></div>
                            <div class="field-wraper">
                                <div class="field_cover"> 
                                    <?php echo $customProductFrm->getFieldHtml('auto_update_other_langs_data'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>

        </div>

        <?php
        if (!empty($otherLanguages)) {
            foreach ($otherLanguages as $langId => $data) {
                $layout = Language::getLayoutDirection($langId);
                ?>
                <div class="accordians_container accordians_container-categories my-3">
                    <div class="accordian_panel">
                        <span class="accordian_title accordianhead" id="collapse_<?php echo $langId; ?>" onclick="translateData(this, '<?php echo $siteDefaultLangId; ?>', '<?php echo $langId; ?>')">
                            <?php
                            echo $data . " ";
                            echo Labels::getLabel('LBL_Language_Data', $adminLangId);
                            ?>
                        </span>
                        <div class="accordian_body accordiancontent layout--<?php echo $layout; ?>" style="display: none;">
                            <div class="p-4 mb-4 bg-gray rounded">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="field-set">
                                            <div class="caption-wraper">
                                                <label class="field_label">
                                                    <?php
                                                    $fld = $customProductFrm->getField('product_name[' . $langId . ']');
                                                    echo $fld->getCaption();
                                                    ?>
                                                </label>
                                            </div>
                                            <div class="field-wraper">
                                                <div class="field_cover">
                                                    <?php echo $customProductFrm->getFieldHtml('product_name[' . $langId . ']'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="field-set">
                                            <div class="caption-wraper">
                                                <label class="field_label">
                                                    <?php
                                                    $fld = $customProductFrm->getField('product_youtube_video[' . $langId . ']');
                                                    echo $fld->getCaption();
                                                    ?>
                                                </label>
                                            </div>
                                            <div class="field-wraper">
                                                <div class="field_cover">
                                                    <?php echo $customProductFrm->getFieldHtml('product_youtube_video[' . $langId . ']'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="field-set">
                                            <div class="caption-wraper">
                                                <label class="field_label">
                                                    <?php
                                                    $fld = $customProductFrm->getField('product_description_' . $langId);
                                                    echo $fld->getCaption();
                                                    ?>
                                                </label>
                                            </div>
                                            <div class="field-wraper">
                                                <div class="field_cover">
                                                    <?php echo $customProductFrm->getFieldHtml('product_description_' . $langId); ?>
                                                </div>
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
        <div class="row">
            <div class="col-md-6">
                <div class="field-set">
                    <div class="caption-wraper"><label class="field_label"></label></div>
                    <div class="field-wraper">
                        <div class="field_cover">
                            <?php /* echo $customProductFrm->getFieldHtml('btn_discard'); */ ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 text-right">
                <div class="field-set">
                    <div class="caption-wraper"><label class="field_label"></label></div>
                    <div class="field-wraper">
                        <div class="field_cover">
                            <?php
                            echo $customProductFrm->getFieldHtml('product_id');
                            echo $customProductFrm->getFieldHtml('product_brand_id');
                            echo $customProductFrm->getFieldHtml('ptc_prodcat_id');
                            echo $customProductFrm->getFieldHtml('ptt_taxcat_id');
                            echo $customProductFrm->getFieldHtml('ptt_taxcat_id_rent');
                            echo $customProductFrm->getFieldHtml('product_type');
                            echo $customProductFrm->getFieldHtml('product_seller_id');
                            echo $customProductFrm->getFieldHtml('preq_id');
                            echo $customProductFrm->getFieldHtml('btn_submit');
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        </form>
        <?php echo $customProductFrm->getExternalJS(); ?>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $('input[name=\'brand_name\']').autocomplete({
            minLength: 0,
            'classes': {
                "ui-autocomplete": "custom-ui-autocomplete"
            },
            'source': function (request, response) {
                $.ajax({
                    url: fcom.makeUrl('brands', 'autoComplete'),
                    data: {keyword: request['term'], fIsAjax: 1, 'fetchAllRecords': 1},
                    dataType: 'json',
                    type: 'post',
                    success: function (json) {
                        response($.map(json, function (item) {
                            return {label: item['name'], value: item['name'], id: item['id']};
                        }));
                    },
                });
            },
            select: function (event, ui) {
                $('input[name=\'product_brand_id\']').val(ui.item.id);
            }
        }).focus(function () {
            $('input[name=\'brand_name\']').autocomplete('search');
        });

        $('input[name=\'brand_name\']').change(function () {
            if ($(this).val() == '') {
                $("input[name='product_brand_id']").val(0);
            }
        });

        $('input[name=\'category_name\']').autocomplete({
            minLength: 0,
            'classes': {
                "ui-autocomplete": "custom-ui-autocomplete"
            },
            'source': function (request, response) {
                $.ajax({
                    url: fcom.makeUrl('productCategories', 'links_autocomplete'),
                    data: {keyword: request['term'], fIsAjax: 1},
                    dataType: 'json',
                    type: 'post',
                    success: function (json) {
                        response($.map(json, function (item) {
                            return {label: item['name'], value: item['name'], id: item['id']};
                        }));
                    },
                });
            },
            select: function (event, ui) {
                $('input[name=\'ptc_prodcat_id\']').val(ui.item.id);
            }
        }).focus(function () {
            $(this).autocomplete('search', $(this).val())
        });

        $('input[name=\'category_name\']').change(function () {
            if ($(this).val() == '') {
                $("input[name='ptc_prodcat_id']").val(0);
            }
        });

        $('input[name=\'taxcat_name\']').autocomplete({
            'classes': {
                "ui-autocomplete": "custom-ui-autocomplete"
            },
            'source': function (request, response) {
                $.ajax({
                    url: fcom.makeUrl('tax', 'autoCompleteTaxCategories'),
                    data: {keyword: request['term'], fIsAjax: 1},
                    dataType: 'json',
                    type: 'post',
                    success: function (json) {
                        response($.map(json, function (item) {
                            return {label: item['name'], value: item['name'], id: item['id']};
                        }));
                    },
                });
            },
            select: function (event, ui) {
                $('input[name=\'ptt_taxcat_id\']').val(ui.item.id);
            }
        });


        $('input[name=\'taxcat_name_rent\']').autocomplete({
            'classes': {
                "ui-autocomplete": "custom-ui-autocomplete"
            },
            'source': function (request, response) {
                $.ajax({
                    url: fcom.makeUrl('tax', 'autoCompleteTaxCategories'),
                    data: {keyword: request['term'], fIsAjax: 1},
                    dataType: 'json',
                    type: 'post',
                    success: function (json) {
                        response($.map(json, function (item) {
                            return {label: item['name'], value: item['name'], id: item['id']};
                        }));
                    },
                });
            },
            select: function (event, ui) {
                $('input[name=\'ptt_taxcat_id_rent\']').val(ui.item.id);
            }
        });
    });
</script>