<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$layout = Language::getLayoutDirection($langId);
?>
<div class="p-4 mb-4 bg-gray rounded" dir="<?php echo $layout; ?>">
    <div class="row">
        <div class="col-md-3">
            <div class="field-set">
                <div class="caption-wraper">
                    <label class="field_label"><?php echo Labels::getLabel('LBL_Specification_Identifier', $siteLangId); ?></label>
                    <span class="spn_must_field">*</span>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <input class="specification-field-js" type="text" name="prodspec_identifier" value="<?php echo (!empty($prodSpecData) && isset($prodSpecData[$langId])) ? $prodSpecData[$langId]['prodspec_identifier'] : ""; ?>">
                        <ul style="display:none;" class="errorlist erlist_specification_<?php echo $langId; ?>"><li><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Specification_Identifier_Is_Mandatory', $siteLangId); ?></a></li></ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="field-set">
                <div class="caption-wraper">
                    <label class="field_label"><?php echo Labels::getLabel('LBL_Specification_Label_Text', $siteLangId); ?></label>
                    <span class="spn_must_field">*</span>
                    <span><i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="<?php echo Labels::getLabel('LBL_Specification_label_tooltip_text', $siteLangId); ?>"></i></span>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <input class="specification-field-js" type="text" name="prodspec_name[<?php echo $langId; ?>]" value="<?php echo (!empty($prodSpecData) && isset($prodSpecData[$langId])) ? $prodSpecData[$langId]['prodspec_name'] : ""; ?>">
                        <ul style="display:none;" class="errorlist erlist_specification_<?php echo $langId; ?>"><li><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Specification_Label_Text_Is_Mandatory', $siteLangId); ?></a></li></ul>                        
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="field-set">
                <div class="caption-wraper">
                    <label class="field_label"><?php echo Labels::getLabel('LBL_Specification_Value', $siteLangId); ?></label>
                    <span class="spn_must_field">*</span>
                    <span><i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="<?php echo Labels::getLabel('LBL_Specification_value_tooltip_text', $siteLangId); ?>"></i></span>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <input class="specification-field-js" type="text" name="prodspec_value[<?php echo $langId; ?>]" value="<?php echo (!empty($prodSpecData) && isset($prodSpecData[$langId])) ? $prodSpecData[$langId]['prodspec_value'] : ""; ?>">
                        <ul style="display:none;" class="errorlist erlist_specification_<?php echo $langId; ?>"><li><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Specification_Value_Is_Mandatory', $siteLangId); ?></a></li></ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="field-set">
                <div class="caption-wraper">
                    <label class="field_label"><?php echo Labels::getLabel('LBL_Specification_Group', $siteLangId); ?></label>
                    <span><i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="<?php echo Labels::getLabel('LBL_Specification_group_tooltip_text', $siteLangId); ?>"></i></span>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <input type="text" class="prodspec_group specification-field-js" name="prodspec_group" value="<?php echo (!empty($prodSpecData) && isset($prodSpecData[$langId])) ? $prodSpecData[$langId]['prodspec_group'] : ""; ?>">
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
                                <input title="<?php echo Labels::getLabel('LBL_Add_Specification_for_other_languages', $siteLangId); ?>" type="checkbox" name="autocomplete_lang_data" value="1" >
                                
                                <?php echo Labels::getLabel('LBL_Translate_To_Other_Languages', $siteLangId); ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>

        <?php if (!empty($otherLanguages)) { ?>
            <div class="col-md-12">
                <?php foreach ($otherLanguages as $otherLangId => $data) {
                    ?>
                    <div class="accordion my-4" id="specification-accordion-<?php echo $otherLangId; ?>">
                        <h6 class="dropdown-toggle toggle-other-lang-data" data-toggle="collapse" data-target="#collapse-<?php echo $otherLangId; ?>" aria-expanded="true" aria-controls="collapse-<?php echo $otherLangId; ?>" onClick="displayOtherLangProdSpec(this,<?php echo $otherLangId; ?>, '<?php echo $langId; ?>')">
                            <span>
                                <?php
                                echo $data . " ";
                                echo Labels::getLabel('LBL_Language_Specification', $siteLangId);
                                ?>
                            </span>
                        </h6>
                        <div id="collapse-<?php echo $otherLangId; ?>" class="collapse collapse-js-<?php echo $otherLangId; ?>" aria-labelledby="headingOne" data-parent="#specification-accordion-<?php echo $otherLangId; ?>">
                            <div class="specifications-form-<?php echo $otherLangId; ?>">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="field-set">
                                            <div class="caption-wraper">
                                                <label class="field_label"><?php echo Labels::getLabel('LBL_Specification_Label_Text', $siteLangId); ?></label>
                                                <span class="spn_must_field">*</span>
                                            </div>
                                            <div class="field-wraper">
                                                <div class="field_cover">
                                                    <input class="specification-field-js" type="text" name="prodspec_name[<?php echo $otherLangId; ?>]" value="<?php echo (!empty($prodSpecData) && isset($prodSpecData[$otherLangId])) ? $prodSpecData[$otherLangId]['prodspec_name'] : ""; ?>">
                                                    <ul style="display:none;" class="errorlist erlist_specification_<?php echo $otherLangId; ?>"><li><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Specification_Label_Text_Is_Mandatory', $siteLangId); ?></a></li></ul>                        
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="field-set">
                                            <div class="caption-wraper">
                                                <label class="field_label"><?php echo Labels::getLabel('LBL_Specification_Value', $siteLangId); ?></label>
                                                <span class="spn_must_field">*</span>
                                            </div>
                                            <div class="field-wraper">
                                                <div class="field_cover">
                                                    <input class="specification-field-js" type="text" name="prodspec_value[<?php echo $otherLangId; ?>]" value="<?php echo (!empty($prodSpecData) && isset($prodSpecData[$otherLangId])) ? $prodSpecData[$otherLangId]['prodspec_value'] : ""; ?>">
                                                    <ul style="display:none;" class="errorlist erlist_specification_<?php echo $otherLangId; ?>"><li><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Specification_Value_Is_Mandatory', $siteLangId); ?></a></li></ul>
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

        <div class="col-md-12 text-right">
            <div class="field-set">
                <div class="caption-wraper">
                    <label class="field_label"></label>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <input type="hidden" name="langId" value="<?php echo $langId; ?>">
                        <input type="hidden" name="prodSpecId" value="<?php echo (!empty($prodSpecData) && isset($prodSpecData[$langId])) ? $prodSpecData[$langId]['prodspec_id'] : 0; ?>">
                        <button type="button" class="btn btn-outline-brand" onClick="saveSpecification()"><?php echo Labels::getLabel('LBL_Add_Specification', $siteLangId) ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var langId = '<?php echo $langId; ?>';
        $('input[name="prodspec_group[' + langId + ']"]').autocomplete({
            'classes': {
                "ui-autocomplete": "custom-ui-autocomplete"
            },
            'source': function (request, response) {
                $.ajax({
                    url: fcom.makeUrl('Seller', 'prodSpecGroupAutoComplete'),
                    data: {keyword: request['term'], langId: langId, fIsAjax: 1},
                    dataType: 'json',
                    type: 'post',
                    success: function (json) {
                        response($.map(json, function (item) {
                            return {label: item['name'], value: item['name']};
                        }));
                    },
                });
            },
            select: function (event, ui) {
                $('input[name="prodspec_group[' + langId + ']"]').val(ui.item.value);
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