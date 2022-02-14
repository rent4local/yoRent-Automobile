<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$layout = Language::getLayoutDirection($langId);
?>
<div class="p-4 mb-4 bg-gray rounded" dir="<?php echo $layout; ?>">
    <div class="row">
        <div class="col-md-5">
            <div class="field-set">
                <div class="caption-wraper">
                    <label class="field_label"><?php echo Labels::getLabel('LBL_Specification_Label_Text', $adminLangId); ?></label>
                    <span class="spn_must_field">*</span>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <input class="specification-field-js" type="text" name="prodspec_name[<?php echo $langId; ?>]" value="<?php echo (!empty($prodSpecData) && isset($prodSpecData['prod_spec_name'][$langId])) ? $prodSpecData['prod_spec_name'][$langId] : ""; ?>">
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
                        <input class="specification-field-js" type="text" name="prodspec_value[<?php echo $langId; ?>]" value="<?php echo (!empty($prodSpecData) && isset($prodSpecData['prod_spec_value'][$langId])) ? $prodSpecData['prod_spec_value'][$langId] : ""; ?>">
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
                        <input type="text" class="prodspec_group specification-field-js" name="prodspec_group" value="<?php echo (!empty($prodSpecData) && isset($prodSpecData['prod_spec_group'][$langId])) ? $prodSpecData['prod_spec_group'][$langId] : ""; ?>">
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
                <?php foreach ($otherLanguages as $otherLangId => $data) {
                    ?>
                    <div class="accordion my-4" id="specification-accordion-<?php echo $otherLangId; ?>">
                        <h6 class="dropdown-toggle toggle-other-lang-data" data-toggle="collapse" data-target="#collapse-<?php echo $otherLangId; ?>" aria-expanded="true" aria-controls="collapse-<?php echo $otherLangId; ?>" onClick="displayOtherLangProdSpec(this,<?php echo $otherLangId; ?>, '<?php echo $langId; ?>')">
                            <span>
                                <?php
                                echo $data . " ";
                                echo Labels::getLabel('LBL_Language_Specification', $adminLangId);
                                ?>
                            </span>
                        </h6>
                        <div id="collapse-<?php echo $otherLangId; ?>" class="collapse collapse-js-<?php echo $otherLangId; ?>" aria-labelledby="headingOne" data-parent="#specification-accordion-<?php echo $otherLangId; ?>">
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
                                                    <input class="specification-field-js" type="text" name="prodspec_name[<?php echo $otherLangId; ?>]" value="<?php echo (!empty($prodSpecData) && isset($prodSpecData['prod_spec_name'][$otherLangId])) ? $prodSpecData['prod_spec_name'][$otherLangId] : ""; ?>">
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
                                                    <input class="specification-field-js" type="text" name="prodspec_value[<?php echo $otherLangId; ?>]" value="<?php echo (!empty($prodSpecData) && isset($prodSpecData['prod_spec_value'][$otherLangId])) ? $prodSpecData['prod_spec_value'][$otherLangId] : ""; ?>">
                                                    <ul style="display:none;" class="errorlist erlist_specification_<?php echo $otherLangId; ?>"><li><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Specification_Value_Is_Mandatory', $adminLangId); ?></a></li></ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php /* <div class="col-md-2">
                                      <div class="field-set">
                                      <div class="caption-wraper">
                                      <label class="field_label"><?php echo Labels::getLabel('LBL_Specification_Group', $adminLangId); ?></label>
                                      </div>
                                      <div class="field-wraper">
                                      <div class="field_cover">
                                      <input type="text" class="prodspec_group specification-field-js" name="prodspec_group[<?php echo $otherLangId; ?>]" value="<?php
                                      if (!empty($prodSpecData)) {
                                      echo $prodSpecData[0]['prodspec_group'];
                                      }
                                      ?>">
                                      </div>
                                      </div>
                                      </div>
                                      </div> */ ?>
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
                        <input type="hidden" name="key" value="<?php echo (!empty($prodSpecData) && isset($prodSpecData['key'][$langId])) ? $prodSpecData['key'][$langId] : -1; ?>">
                        <button type="button" class="btn btn-outline-brand " onClick="saveSpecification()"><?php echo Labels::getLabel('LBL_Add_Specification', $adminLangId) ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var langId = '<?php echo $langId; ?>';
        $('input[name="prodspec_group"]').autocomplete({
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
                                value: item['name'],
                                id: item['name']
                            };
                        }));
                    },
                });
            },
            'select': function (event, ui) {
                $('input[name="prodspec_group[' + langId + ']"]').val(ui.item.id);
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
