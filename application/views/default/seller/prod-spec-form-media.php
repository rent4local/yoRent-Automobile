<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$layout = Language::getLayoutDirection($langId);
?>
<form method="post" enctype="multipart/form-data" name="spectifction_media_frm" id="spectifction_media_frm" class="attr-spec-frm--js form form--horizontal">
    <div class="web_form p-4 mb-4 bg-gray rounded">
        <div class="row">
			<div class="col-md-12 mb-3">
				<h5><?php echo Labels::getLabel('LBL_File_Specification_Details', $siteLangId); ?></h5>
			</div>
		
            <div class="col-md-4">
                <div class="field-set">
                    <div class="caption-wraper">
                        <label class="field_label"><?php echo Labels::getLabel('LBL_Specification_Identifier', $siteLangId); ?></label>
                        <span class="spn_must_field">*</span>
                    </div>
                    <div class="field-wraper">
                        <div class="field_cover">
                            <input class="specification-field-js" type="text" name="prodspec_identifier" value="<?php echo (!empty($prodSpecData) && isset($prodSpecData[$langId])) ? $prodSpecData[$langId]['prodspec_identifier'] : ""; ?>">
                            <ul style="display:none;" class="errorlist erlist_specification_<?php echo $langId; ?>"><li><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Specification_Label_Identifier_Is_Mandatory', $siteLangId); ?></a></li></ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="field-set">
                    <div class="caption-wraper">
                        <label class="field_label"><?php echo Labels::getLabel('LBL_File_Title', $siteLangId); ?></label>
                        <span class="spn_must_field">*</span>
                    </div>
                    <div class="field-wraper">
                        <div class="field_cover">
                            <input class="specification-field-js" type="hidden" name="prodspec_value[<?php echo $langId; ?>]" value="">
                            <input class="specification-field-js" type="text" name="prodspec_name[<?php echo $langId; ?>]" value="<?php echo (!empty($prodSpecData) && isset($prodSpecData[$langId])) ? $prodSpecData[$langId]['prodspec_name'] : ""; ?>">
                            <ul style="display:none;" class="errorlist erlist_specification_<?php echo $langId; ?>"><li><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Specification_Label_Text_Is_Mandatory', $siteLangId); ?></a></li></ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <br />
                <div class="field-wraper">
                    <div class="field_cover">
                        <span class="filename"></span><input id="prodspec_files_<?php echo $langId; ?>" type="file" name="prodspec_files_<?php echo $langId; ?>" data-langid="<?php echo $langId; ?>" onChange="popupSpecificationFile(<?php echo $langId; ?>);">
                        <ul style="display:none;" class="errorlist erlist_specification_media<?php echo $langId; ?>"><li><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Specification_File_Is_Mandatory', $siteLangId); ?></a></li></ul>
                        <br>
                    </div>
                    <div id="filePreviewDiv_<?php echo $langId; ?>"></div>
                </div>
            </div>
            <?php /* <div class="col-md-3">
                <div class="field-set">
                    <div class="caption-wraper">
                        <label class="field_label"><?php echo Labels::getLabel('LBL_Specification_Group', $siteLangId); ?></label>
                    </div>
                    <div class="field-wraper">
                        <div class="field_cover">
                            <input class="specification-field-js" type="text" class="prodspec_group" name="prodspec_group" value="<?php echo (!empty($prodSpecData) && isset($prodSpecData[$langId])) ? $prodSpecData[$langId]['prodspec_group'] : ""; ?>">
                        </div>
                    </div>
                </div>
            </div> */ ?>
            <?php if ($langId == $siteDefaultLangId && !empty(FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, ''))) { ?>
                <div class="col-md-12">
                    <div class="field-set">
                        <div class="field-wraper">
                            <div class="field_cover">
                                <label class="checkbox">
                                    <input title="<?php echo Labels::getLabel('LBL_Translate_To_Other_Languages', $siteLangId); ?>" type="checkbox" name="autocomplete_lang_data" value="1" >
                                    
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
                                        <div class="col-md-9">
                                            <div class="field-set">
                                                <div class="caption-wraper">
                                                    <label class="field_label"><?php echo Labels::getLabel('LBL_File_Title', $siteLangId); ?></label>
                                                    <span class="spn_must_field">*</span>
                                                </div>
                                                <div class="field-wraper">
                                                    <div class="field_cover">
                                                        <input class="specification-field-js" type="hidden" name="prodspec_value[<?php echo $otherLangId; ?>]" value="">
                                                        <input class="specification-field-js" type="text" name="prodspec_name[<?php echo $otherLangId; ?>]" value="<?php echo (!empty($prodSpecData) && isset($prodSpecData[$otherLangId])) ? $prodSpecData[$otherLangId]['prodspec_name'] : ""; ?>">
                                                        <ul style="display:none;" class="errorlist erlist_specification_<?php echo $otherLangId; ?>"><li><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Specification_Label_Text_Is_Mandatory', $siteLangId); ?></a></li></ul>                        
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <br />
                                            <div class="field-wraper">
                                                <div class="field_cover">
                                                    <span class="filename"></span><input id="prodspec_files_<?php echo $otherLangId; ?>" type="file" name="prodspec_files_<?php echo $otherLangId; ?>" data-langid="<?php echo $otherLangId; ?>" onChange="popupSpecificationFile(<?php echo $otherLangId; ?>);">
                                                    <ul style="display:none;" class="errorlist erlist_specification_media<?php echo $otherLangId; ?>"><li><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Specification_File_Is_Mandatory', $siteLangId); ?></a></li></ul>
                                                    <br>
                                                </div>
                                                <div id="filePreviewDiv_<?php echo $otherLangId; ?>"></div>
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
                    <div class="caption-wraper"></div>
                    <div class="field-wraper">
                        <div class="field_cover">
                            <input type="hidden" name="langId" value="<?php echo $langId; ?>">
                            <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                            <input type="hidden" name="isFileForm" value="1">
                            <input type="hidden" name="fileUploaded" value="0">
                            <input type="hidden" name="prodSpecId" value="<?php echo (!empty($prodSpecData) && isset($prodSpecData[$langId])) ? $prodSpecData[$langId]['prodspec_id'] : 0; ?>">
                            <button type="button" class="btn btn-outline-brand" onClick="saveSpecificationWithFile()"><?php echo Labels::getLabel('LBL_Add_Specification', $siteLangId) ?></button></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

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
<style>
    input[type="file"] {
        max-width: 100%;
    }
</style>
