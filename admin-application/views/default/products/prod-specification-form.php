<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
?>
<div class="web_form p-4 mb-4 bg-gray rounded">
    <div class="row">
        <div class="col-md-5">
            <div class="field-set">
                <div class="caption-wraper">
                    <label class="field_label"><?php echo Labels::getLabel('LBL_Specification_Label_Text', $adminLangId); ?></label>
                    <span class="spn_must_field">*</span>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <input class="specification-field-js" type="text" name="prodspec_name[<?php echo $langId; ?>]" value="<?php echo (!empty($prodSpecData) && isset($prodSpecData[$langId])) ? $prodSpecData[$langId]['prodspec_name'] : ""; ?>">
                        <ul style="display:none;" class="errorlist erlist_specification_<?php echo $langId; ?>"><li><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Specification_Label_Text_Is_Mandatory', $adminLangId); ?></a></li></ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="field-set">
                <div class="caption-wraper">
                    <label class="field_label"><?php echo Labels::getLabel('LBL_Specification_Value', $adminLangId); ?></label>
                    <span class="spn_must_field">*</span>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <input class="specification-field-js" type="text" name="prodspec_value[<?php echo $langId; ?>]" value="<?php echo (!empty($prodSpecData) && isset($prodSpecData[$langId])) ? $prodSpecData[$langId]['prodspec_value'] : ""; ?>">
                        <ul style="display:none;" class="errorlist erlist_specification_<?php echo $langId; ?>"><li><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Specification_Value_Is_Mandatory', $adminLangId); ?></a></li></ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="field-set">
                <div class="caption-wraper">
                    <label class="field_label"><?php echo Labels::getLabel('LBL_Specification_Group', $adminLangId); ?></label>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <input class="specification-field-js" type="text" id="prodspec_group" class="prodspec_group" name="prodspec_group" value="<?php echo (!empty($prodSpecData) && isset($prodSpecData[$langId])) ? $prodSpecData[$langId]['prodspec_group'] : ""; ?>">
                    </div>
                </div>
            </div>
        </div>
        <?php if ($langId == $siteDefaultLangId && !empty(FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, ''))) { ?>
            <div class="col-md-12">
                <div class="field-set">
                    <div class="field-wraper">
                        <div class="field_cover">
                            <label class="checkbox">
                                <input title="<?php echo Labels::getLabel('LBL_Translate_To_Other_Languages', $adminLangId); ?>" type="checkbox" name="autocomplete_lang_data" value="1" >
                                
                                <?php echo Labels::getLabel('LBL_Translate_To_Other_Languages', $adminLangId); ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
        <?php if (!empty($otherLanguages)) { ?>
            <div class="col-md-12">
                <?php
                foreach ($otherLanguages as $otherLangId => $data) {
                    $layout = Language::getLayoutDirection($otherLangId);
                    ?>
                    <div class="accordians_container accordians_container-categories">
                        <div class="accordian_panel">
                            <span class="accordian_title accordianhead" onClick="displayOtherLangProdSpec(this,<?php echo $otherLangId; ?>, <?php echo $langId; ?>)">
                                <?php
                                echo $data . " ";
                                echo Labels::getLabel('LBL_Language_Specification', $adminLangId);
                                ?>
                            </span>
                            <div class="accordian_body accordiancontent p-0 layout--<?php echo $layout; ?>" style="display: none;">
                                <div class="specifications-form-<?php echo $otherLangId; ?>">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="field-set">
                                                <div class="caption-wraper">
                                                    <label class="field_label"><?php echo Labels::getLabel('LBL_Specification_Label_Text', $adminLangId); ?></label>
                                                    <span class="spn_must_field">*</span>
                                                </div>
                                                <div class="field-wraper">
                                                    <div class="field_cover">
                                                        <input class="specification-field-js" type="text" name="prodspec_name[<?php echo $otherLangId; ?>]" value="<?php echo (!empty($prodSpecData) && isset($prodSpecData[$otherLangId])) ? $prodSpecData[$otherLangId]['prodspec_name'] : ""; ?>">
                                                        <ul style="display:none;" class="errorlist erlist_specification_<?php echo $otherLangId; ?>"><li><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Specification_Label_Text_Is_Mandatory', $adminLangId); ?></a></li></ul>                        
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="field-set">
                                                <div class="caption-wraper">
                                                    <label class="field_label"><?php echo Labels::getLabel('LBL_Specification_Value', $adminLangId); ?></label>
                                                    <span class="spn_must_field">*</span>
                                                </div>
                                                <div class="field-wraper">
                                                    <div class="field_cover">
                                                        <input class="specification-field-js" type="text" name="prodspec_value[<?php echo $otherLangId; ?>]" value="<?php echo (!empty($prodSpecData) && isset($prodSpecData[$otherLangId])) ? $prodSpecData[$otherLangId]['prodspec_value'] : ""; ?>">
                                                        <ul style="display:none;" class="errorlist erlist_specification_<?php echo $otherLangId; ?>"><li><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Specification_Value_Is_Mandatory', $adminLangId); ?></a></li></ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php }
                ?>
            </div>
        <?php }
        ?>
        <div class="col-md-12  text-right">
            <div class="field-set">
                <div class="caption-wraper"></div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <input type="hidden" name="langId" value="<?php echo $langId; ?>">
                        <input type="hidden" name="prodSpecId" value="<?php echo (!empty($prodSpecData) && isset($prodSpecData[$langId])) ? $prodSpecData[$langId]['prodspec_id'] : 0; ?>">
                        <button type="button" class="btn btn-brand" onClick="saveSpecification()"><?php echo Labels::getLabel('LBL_Add_Specification', $adminLangId) ?></button></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var langId = '<?php echo $langId; ?>';
        $('input[name="prodspec_group').autocomplete({
            'classes': {
                "ui-autocomplete": "custom-ui-autocomplete"
            },
            'source': function (request, response) {
                $.ajax({
                    url: fcom.makeUrl('products', 'prodSpecGroupAutoComplete'),
                    data: {keyword: request['term'], langId: langId, fIsAjax: 1},
                    dataType: 'json',
                    type: 'post',
                    success: function (json) {
                        response($.map(json, function (item) {
                            return {
                                label: item['name'],
                                value: item['name']
                            };
                        }));
                    },
                });
            },
            'select': function (event, ui) {
                $('input[name="prodspec_group"]').val(ui.item.value);
                return false;
            }

        });

        $('.specification-field-js').on('keyup keypress', function (e) {
            var keyCode = e.keyCode || e.which;
            if (keyCode === 13) {
                e.preventDefault();
                return false;
            }
        });
    });
</script>