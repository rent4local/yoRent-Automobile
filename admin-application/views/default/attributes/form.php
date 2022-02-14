<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->setFormTagAttribute('onsubmit', 'setupAttr(this); return(false);');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;

$clearFld = $frm->getField('btn_discard');
$clearFld->setFieldTagAttribute('onclick', 'categoryCustomFieldsForm("'.$prodCatId.'")'); 

$attrtypeFld = $frm->getField('attr_type');
$attrtypeFld->setFieldTagAttribute('id', 'attr-type-js'); 
if ($attrId > 0) {
    $attrtypeFld->setFieldTagAttribute('disabled', 'disabled'); 
}
?>
<div id="attr_form">
    <?php echo $frm->getFormTag(); ?>
    <div class="row">
        <div class="col-md-12">
            <div class="field-set">
                <div class="caption-wraper">
                    <label class="field_label"><?php echo $frm->getField('attr_name['.$siteDefaultLangId.']')->getCaption(); ?>
                    <span class="spn_must_field">*</span></label>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <?php echo $frm->getFieldHtml('attr_name['.$siteDefaultLangId.']'); ?>
                    </div>
                </div>
           </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="field-set">
                <div class="caption-wraper">
                    <label class="field_label"><?php echo $frm->getField('attr_type')->getCaption(); ?>
                    <span class="spn_must_field">*</span></label>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <?php echo $frm->getFieldHtml('attr_type'); ?>
                    </div>
                </div>
           </div>
        </div>
    </div>
    <div class="row attr-options-js">
        <div class="col-md-12">
            <div class="field-set">
                <div class="caption-wraper">
                    <label class="field_label"><?php echo $frm->getField('attr_options['.$siteDefaultLangId.']')->getCaption(); ?>
                    </label>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <?php echo $frm->getFieldHtml('attr_options['.$siteDefaultLangId.']'); ?>
                    </div>
                </div>
           </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="field-set">
                <div class="caption-wraper">
                    <label class="field_label"><?php echo $frm->getField('attr_postfix['.$siteDefaultLangId.']')->getCaption(); ?>
                    </label>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <?php echo $frm->getFieldHtml('attr_postfix['.$siteDefaultLangId.']'); ?>
                    </div>
                </div>
           </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="field-set">
                <div class="caption-wraper">
                    <label class="field_label"><?php echo $frm->getField('attrgrp_name['.$siteDefaultLangId.']')->getCaption(); ?>
                    </label>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <?php echo $frm->getFieldHtml('attrgrp_name['.$siteDefaultLangId.']'); ?>
                    </div>
                </div>
           </div>
        </div>
    </div>
    <div class="row display-in-filter-field-js">
        <div class="col-md-12">
            <div class="field-set">
                <div class="caption-wraper">
                    <label class="field_label"><?php echo $frm->getField('attr_display_in_filter')->getCaption(); ?>
                    </label>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <?php echo $frm->getFieldHtml('attr_display_in_filter'); ?>
                    </div>
                </div>
           </div>
        </div>
    </div>
	<?php if (!empty($frm->getField('attr_display_in_listing'))) { ?>
	<div class="row">
        <div class="col-md-12">
            <div class="field-set">
                <div class="caption-wraper">
                    <label class="field_label"><?php echo $frm->getField('attr_display_in_listing')->getCaption(); ?>
                    </label>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <?php echo $frm->getFieldHtml('attr_display_in_listing'); ?>
                    </div>
                </div>
           </div>
        </div>
    </div>
	<?php } ?>
	

    <!-- BOC Other Language form fields -->
    <?php if(!empty($otherLangData)){
    foreach($otherLangData as $langId=>$data) { 
    ?>
        <div class="accordians_container accordians_container-categories" defaultLang= "<?php echo $siteDefaultLangId; ?>" language="<?php echo $langId; ?>" id="accordion-language_<?php echo $langId; ?>" onClick="translateData(this)">
             <div class="accordian_panel">
                 <span class="accordian_title accordianhead accordian_title" id="collapse_<?php echo $langId; ?>">
                 <?php echo $data." "; echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                 </span>
                 <div class="accordian_body accordiancontent" style="display: none;">
                     <div class="row">
                        <div class="col-md-12">
                            <div class="field-set">
                                <div class="caption-wraper">
                                    <label class="field_label"><?php echo $frm->getField('attr_name['.$langId.']')->getCaption(); ?>
                                    </label>
                                </div>
                                <div class="field-wraper">
                                    <div class="field_cover">
                                        <?php echo $frm->getFieldHtml('attr_name['.$langId.']'); ?>
                                    </div>
                                </div>
                           </div>
                        </div>
                    </div>
                    <div class="row attr-options-js">
                        <div class="col-md-12">
                            <div class="field-set">
                                <div class="caption-wraper">
                                    <label class="field_label"><?php echo $frm->getField('attr_options['.$langId.']')->getCaption(); ?>
                                    </label>
                                </div>
                                <div class="field-wraper">
                                    <div class="field_cover">
                                        <?php echo $frm->getFieldHtml('attr_options['.$langId.']'); ?>
                                    </div>
                                </div>
                           </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="field-set">
                                <div class="caption-wraper">
                                    <label class="field_label"><?php echo $frm->getField('attr_postfix['.$langId.']')->getCaption(); ?>
                                    </label>
                                </div>
                                <div class="field-wraper">
                                    <div class="field_cover">
                                        <?php echo $frm->getFieldHtml('attr_postfix['.$langId.']'); ?>
                                    </div>
                                </div>
                           </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="field-set">
                                <div class="caption-wraper">
                                    <label class="field_label"><?php echo $frm->getField('attrgrp_name['.$langId.']')->getCaption(); ?>
                                    </label>
                                </div>
                                <div class="field-wraper">
                                    <div class="field_cover">
                                        <?php echo $frm->getFieldHtml('attrgrp_name['.$langId.']'); ?>
                                    </div>
                                </div>
                           </div>
                        </div>
                    </div>
                 </div>
             </div>
         </div>
    <?php } 
    }
    ?>
    <!-- Eoc Other Language form fields -->
    <div class="row tabs_footer mt-3">
        <div class="col-md-6">
            <?php echo $frm->getFieldHtml('btn_discard'); ?>
        </div>
        <div class="col-md-6 text-right">
            <?php echo $frm->getFieldHtml('btn_submit'); ?>
        </div>
    </div>
    <?php 
    echo $frm->getFieldHtml('attr_id'); 
    echo $frm->getFieldHtml('attr_prodcat_id'); 
    echo $frm->getFieldHtml('attr_attrgrp_id'); 
    ?>
    </form>
    <?php echo $frm->getExternalJS(); ?>
</div>
            
<script type="text/javascript">
$(document).ready(function() {
	var otherLangData = '<?php echo json_encode($otherLangData); ?>';
    var langId = '<?php echo $siteDefaultLangId; ?>';
    
    $('input[name="attrgrp_name['+langId+']"]').autocomplete({
        'classes': {
            "ui-autocomplete": "custom-ui-autocomplete"
        },
        'source': function(request, response) {
            $('input[name="attr_attrgrp_id"]').val('');
            $.each(JSON.parse(otherLangData), function(key, value){
				if ($('input[name="attrgrp_name['+key+']"]').length > 0) {
					$('input[name="attrgrp_name['+key+']"]').val('');
				}
            });
            
            $.ajax({
                url: fcom.makeUrl('attributeGroups', 'autocomplete'),
                data: {keyword: request['term'], langId: langId, fIsAjax:1},
                dataType: 'json',
                type: 'post',
                success: function(json) {
                    response($.map(json, function(item) {
                        return {
                            label: item['name'],
                            value: item['id'],
                            identifier: item['attrgrp_identifier'],
                            etc: item['otherLangData'],
                            };
                    }));
                },
            });
        },
        'select': function(event, ui) {
			
            $('input[name="attrgrp_name['+langId+']"]').val(ui.item.label);
            $('input[name="attr_attrgrp_id"]').val(ui.item.value);
			
			$.each(JSON.parse(otherLangData), function(key, value){
				if ($('input[name="attrgrp_name['+key+']"]').length > 0) {
					$('input[name="attrgrp_name['+key+']"]').val(ui.item.etc[key]);
				}
            });
            return false;
        }

    });
});

$('#attr-type-js').trigger('change');
</script>