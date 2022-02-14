<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
if ($selprod_id > 0 || empty($productOptions)) {
    $frmSellerProduct->setFormTagAttribute('onsubmit', 'setUpSellerProduct(this); return(false);');
} else {
    $frmSellerProduct->setFormTagAttribute('onsubmit', 'setUpMultipleSellerProducts(this); return(false);');
}
$siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
$frmSellerProduct->setFormTagAttribute('class', 'form form--horizontal layout--' . Language::getLayoutDirection($siteDefaultLangId));
$autoUpdateFld = $frmSellerProduct->getField('auto_update_other_langs_data');
if (null != $autoUpdateFld) {
    $autoUpdateFld->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
    $autoUpdateFld->developerTags['cbHtmlAfterCheckbox'] = '';
}

$urlFld = $frmSellerProduct->getField('selprod_url_keyword');
$urlFld->setFieldTagAttribute('id', "urlrewrite_custom");
/* $urlFld->setFieldTagAttribute('onkeyup', "getSlugUrl(this,this.value, $selprod_id, 'post')"); */
$urlFld->setFieldTagAttribute('onkeyup', "getUniqueSlugUrl(this,this.value,$selprod_id)");
$urlFld->htmlAfterField = "<span class='form-text text-muted'>" . UrlHelper::generateFullUrl('Products', 'View', array($selprod_id), '/') . '</span>';

$fld = $frmSellerProduct->getField('selprod_enable_rfq');
$fld->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
$fld->developerTags['cbHtmlAfterCheckbox'] = '';


$submitBtnFld = $frmSellerProduct->getField('btn_submit');
$submitBtnFld->setFieldTagAttribute('class', 'btn btn-brand');
$submitBtnFld->developerTags['col'] = 12;

$cancelBtnFld = $frmSellerProduct->getField('btn_cancel');
$cancelBtnFld->setFieldTagAttribute('class', 'btn btn-outline-brand js-cancel-inventory');
?>
<div class="row">
    <div class="col-md-12">
        <div class="form__subcontent">
            <?php echo $frmSellerProduct->getFormTag(); ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="field-set">
                        <div class="caption-wraper"><label class="field_label"><?php echo $frmSellerProduct->getField('selprod_title' . $siteDefaultLangId)->getCaption(); ?><span
                                    class="spn_must_field">*</span></label></div>
                        <div class="field-wraper">
                            <div class="field_cover"><?php echo $frmSellerProduct->getFieldHtml('selprod_title' . $siteDefaultLangId); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="field-set">
                        <div class="caption-wraper"><label class="field_label"><?php echo $frmSellerProduct->getField('selprod_url_keyword')->getCaption(); ?><span
                                    class="spn_must_field">*</span> <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right"
                                           title="<?php echo Labels::getLabel('LBL_Keyword_to_be_used_in_url_of_the_product', $siteDefaultLangId); ?>"></i></label></div>
                        <div class="field-wraper">
                            <div class="field_cover"><?php echo $frmSellerProduct->getFieldHtml('selprod_url_keyword'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="field-set">
                        <div class="caption-wraper"><label class="field_label"><?php echo $frmSellerProduct->getField('sprodata_rental_active')->getCaption(); ?></label>
                        </div>
                        <div class="field-wraper">
                            <div class="field_cover"><?php echo $frmSellerProduct->getFieldHtml('sprodata_rental_active'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="field-set">
                        <div class="caption-wraper"><label class="field_label"><?php echo $frmSellerProduct->getField('sprodata_rental_available_from')->getCaption(); ?><span
                                    class="spn_must_field">*</span> <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right"
                                           title="<?php echo Labels::getLabel('LBL_Select_the_date_from_which_the_current_inventory_can_be_available.', $siteDefaultLangId); ?>"></i></label></div>
                        <div class="field-wraper">
                            <div class="field_cover"><?php echo $frmSellerProduct->getFieldHtml('sprodata_rental_available_from'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="field-set">
                        <div class="caption-wraper"><label class="field_label"><?php echo $frmSellerProduct->getField('sprodata_rental_condition')->getCaption(); ?><span
                                    class="spn_must_field">*</span></label></div>
                        <div class="field-wraper">
                            <div class="field_cover"><?php echo $frmSellerProduct->getFieldHtml('sprodata_rental_condition'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="selprod_cod_enabled_fld col-md-6">
                    <div class="field-set">
                        <div class="caption-wraper"><label class="field_label"><?php echo $frmSellerProduct->getField('selprod_cod_enabled')->getCaption(); ?></label>
                        </div>
                        <div class="field-wraper">
                            <div class="field_cover"><?php echo $frmSellerProduct->getFieldHtml('selprod_cod_enabled'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="field-set">
                        <div class="caption-wraper"><label class="field_label"><?php echo $frmSellerProduct->getField('sprodata_duration_type')->getCaption(); ?> <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right"
                                           title="<?php echo Labels::getLabel('LBL_Select_the_duration_type_for_the_rental._It_can_be_in_days,_weeks_or_months..', $siteDefaultLangId); ?>"></i></label>
                        </div>
                        <div class="field-wraper">
                            <div class="field_cover"><?php echo $frmSellerProduct->getFieldHtml('sprodata_duration_type'); ?>
                            </div>
                            <span class="note text-danger"><?php echo Labels::getLabel('LBL_Duration_discount_may_be_affected_after_duration_type_change', $siteLangId); ?></span>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="field-set">
                        <div class="caption-wraper"><label class="field_label"><?php echo $frmSellerProduct->getField('sprodata_minimum_rental_duration')->getCaption(); ?></label>
                        </div>
                        <div class="field-wraper">
                            <div class="field_cover"><?php echo $frmSellerProduct->getFieldHtml('sprodata_minimum_rental_duration'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                
               <?php //if (!empty($shipBySeller)) { ?>
                    <div class="selprod_fulfillment_type_fld col-md-6">
                        <div class="field-set">
                            <div class="caption-wraper"><label class="field_label"><?php echo $frmSellerProduct->getField('sprodata_fullfillment_type')->getCaption(); ?><span class="spn_must_field">*</span></label>
                            </div>
                            <div class="field-wraper">
                                <div class="field_cover"><?php echo $frmSellerProduct->getFieldHtml('sprodata_fullfillment_type'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                
                     <div class="selprod_fulfillment_type_fld col-md-6">
                        <div class="field-set">
                            <div class="caption-wraper"><label class="field_label"><?php echo $frmSellerProduct->getField('shipping_profile')->getCaption(); ?><span class="spn_must_field">*</span></label>
                            </div>
                            <div class="field-wraper">
                                <div class="field_cover"><?php echo $frmSellerProduct->getFieldHtml('shipping_profile'); ?></div>
                                <span class="note text-danger"><?php echo Labels::getLabel('LBL_Profile_will_Update_for_all_Invetories_of_Same_catalog_for_sale_and_rent', $siteLangId); ?></span>
                            </div>
                        </div>
                    </div>
                <?php //} ?>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="field-set">
                        <div class="caption-wraper"><label class="field_label"><?php echo $frmSellerProduct->getField('sprodata_minimum_rental_quantity')->getCaption(); ?>
                                <span class="spn_must_field">*</span></label></div>
                        <div class="field-wraper">
                            <div class="field_cover"><?php echo $frmSellerProduct->getFieldHtml('sprodata_minimum_rental_quantity'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if(FatApp::getConfig('CONF_ENABLE_RFQ_MODULE_WITH_PRODUCTS', FatUtility::VAR_INT, 0)) { ?>
                <div class="col-md-6">
                    <div class="field-set">
                        <div class="caption-wraper"><label class="field_label"></label></div>
                        <div class="field-wraper">
                            <div class="field_cover"><?php echo $frmSellerProduct->getFieldHtml('selprod_enable_rfq'); ?> <label class="field_label"> <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right"
                                           title="<?php echo Labels::getLabel('LBL_RFQ_tooltip_text', $siteDefaultLangId); ?>"></i></label>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                } ?>
            </div> 
            

            <div class="row">
                <div class="col-md-12">
                    <div class="scroll scroll-x js-scrollable table-wrap">
                        <table id="optionsTable-js" class="table table-justified">
                            <thead>
                                <tr>
                                    <?php if ($selprod_id == 0 && !empty($availableOptions)) { ?>
                                        <th><?php echo Labels::getLabel('LBL_Variant/Option', $siteLangId); ?></th>
                                    <?php } ?>
                                    <th><?php echo Labels::getLabel('LBL_Security_Amount', $siteLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_Buffer_Days', $siteLangId); ?> <i class="tabs-icon fa fa-info-circle"  data-toggle="tooltip" data-placement="right" title="<?php echo Labels::getLabel('LBL_Buffer_Days_Detail', $siteLangId); ?>">
                                </i></th>
                                    <th><?php echo Labels::getLabel('LBL_Original_Price', $siteLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_Rental_Price', $siteLangId); ?>
                                        <?php if(FatApp::getConfig('CONF_PRODUCT_INCLUSIVE_TAX', FatUtility::VAR_INT, 0)) {
                                            echo "<span class=''>(". Labels::getLabel('LBL_Including_Tax', $siteLangId)  .")</span>";
                                        } ?>
                                    </th>
                                    <th class=""><?php echo Labels::getLabel('LBL_Quantity', $siteLangId); ?></th>
                                    <?php if (($selprod_id == 0 && !empty($availableOptions)) || !empty($optionValues)) { ?>
                                        <th></th>
                                        <th></th>
                                        
                                    <?php } ?>
                                </tr>
                            </thead>
                            <tbody>

                            <input type="hidden" name="variantOptionCount" value="<?php echo count($availableOptions); ?>">
                            <tr class="formFields--js">
                                <?php if ($selprod_id == 0 && !empty($availableOptions)) { ?>
                                    <td class="optionFld-js">
                                        <?php
                                        $optFld = $frmSellerProduct->getField('varient_id');
                                        $optFld->setFieldTagAttribute('onChange', 'updateFieldNames(this)');
                                        $optFld->setFieldTagAttribute('id', 'varient_id--js');
                                        echo $frmSellerProduct->getFieldHtml('varient_id');
                                        ?>
                                    </td>
                                <?php } ?>
                                <td class="optionFld-js">
                                    <?php
                                    $secAmtFld = $frmSellerProduct->getField('sprodata_rental_security');
                                    $secAmtFld->setFieldTagAttribute('id', 'securityFld--js');
                                    echo $frmSellerProduct->getFieldHtml('sprodata_rental_security');
                                    ?>
                                </td>
                                <td class="optionFld-js">
                                    <?php
                                    $bufferDayFld = $frmSellerProduct->getField('sprodata_rental_buffer_days');
                                    $bufferDayFld->setFieldTagAttribute('id', 'bufferDaysFld--js');
                                    echo $frmSellerProduct->getFieldHtml('sprodata_rental_buffer_days');
                                    ?>
                                </td>
                                <td class="optionFld-js">
                                    <?php
                                    $priceFld = $frmSellerProduct->getField('selprod_cost');
                                    $priceFld->setFieldTagAttribute('id', 'selprodCostFld--js');
                                    echo $frmSellerProduct->getFieldHtml('selprod_cost');
                                    ?>
                                </td>
                                <td class="optionFld-js ">
                                    <div class="price-flds">
                                        <?php
                                        $priceFld = $frmSellerProduct->getField('sprodata_rental_price');
                                        $priceFld->setFieldTagAttribute('id', 'rentalPriceFld--js');
                                        echo $frmSellerProduct->getFieldHtml('sprodata_rental_price');
                                        
                                        ?>
                                    </div>
                                </td>
                                <td class="optionFld-js">
                                    <?php
                                    $fld = $frmSellerProduct->getField('sprodata_rental_stock');
                                    $fld->setFieldTagAttribute('id', 'rentalStockFld--js');
                                    echo $frmSellerProduct->getFieldHtml('sprodata_rental_stock');
                                    ?>
                                </td>
                                <?php if ($selprod_id == 0 && !empty($availableOptions)) { ?>
                                    <td class="action-btn--js">
                                        <button title="<?php echo Labels::getLabel('LBL_Add_Option', $siteLangId); ?>" onClick="addMoreOptionRow()" type="button" class="btn btn-secondary btn-icon  btn-add-row--js">
                                            <i class="icn"> 
                                            <svg class="svg" width="16px" height="16px">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#plus"></use>
                                            </svg>
                                            </i>
                                        </button>
                                    </td>
                                    <!-- COPY OPTION DATA -->
                                    <td>
                                        <button disabled="disabled" onClick="copyRowData(this)" type="button" class="js-copy-btn btn btn-secondary btn-elevate btn-icon copy-btn--js" title="<?php echo Labels::getLabel('LBL_Copy_to_clipboard', $siteLangId) ?>">
                                            <i class="fas fa-paste"></i>
                                        </button>
                                    </td>
                                    <!--  -->
                                    
                                <?php } ?>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="field-set">
                        <div class="caption-wraper">
                            <label class="field_label">
                                <?php echo $frmSellerProduct->getField('selprod_comments' . $siteDefaultLangId)->getCaption(); ?>
                            </label>
                        </div>
                        <div class="field-wraper">
                            <div class="field_cover"><?php echo $frmSellerProduct->getFieldHtml('selprod_comments' . $siteDefaultLangId); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="field-set">
                        <div class="caption-wraper">
                            <label class="field_label">
                                <?php echo $frmSellerProduct->getField('selprod_rental_terms' . $siteDefaultLangId)->getCaption(); ?>
                            </label>
                        </div>
                        <div class="field-wraper">
                            <div class="field_cover"><?php echo $frmSellerProduct->getFieldHtml('selprod_rental_terms' . $siteDefaultLangId); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            $languages = Language::getAllNames();
            unset($languages[$siteDefaultLangId]);
            $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
            if (!empty($translatorSubscriptionKey) && count($languages) > 0) {
                ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="field-set mb-0">
                            <div class="caption-wraper"></div>
                            <div class="field-wraper">
                                <div class="field_cover">
                                    <?php echo $frmSellerProduct->getFieldHtml('auto_update_other_langs_data'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <?php if (count($languages) > 0) { ?>
                <div class="row">
                    <div class="col-md-12">
                        <?php
                        foreach ($languages as $langId => $langName) {
                            $layout = Language::getLayoutDirection($langId);
                            ?>
                            <div class="accordion mt-4" id="specification-accordion">
                                <h6 class="dropdown-toggle" data-toggle="collapse" data-target="#collapseOne<?php echo $langId; ?>"
                                    aria-expanded="true" aria-controls="collapseOne<?php echo $langId; ?>"><span
                                        onclick="translateData(this, '<?php echo $siteDefaultLangId; ?>', '<?php echo $langId; ?>')">
                                            <?php echo Labels::getLabel('LBL_Inventory_Data_for', $siteLangId); ?>
                                            <?php echo $langName; ?>
                                    </span>
                                </h6>
                                <div id="collapseOne<?php echo $langId; ?>"
                                     class="collapse collapse-js-<?php echo $langId; ?>"
                                     aria-labelledby="headingOne" data-parent="#specification-accordion">
                                    <div class="p-4 mb-4 bg-gray rounded"
                                         dir="<?php echo $layout; ?>">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="field-set">
                                                    <div class="caption-wraper"><label class="field_label"><?php echo $frmSellerProduct->getField('selprod_title' . $langId)->getCaption(); ?></label>
                                                    </div>
                                                    <div class="field-wraper">
                                                        <div class="field_cover"><?php echo $frmSellerProduct->getFieldHtml('selprod_title' . $langId); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="field-set">
                                                    <div class="caption-wraper"><label class="field_label"><?php echo $frmSellerProduct->getField('selprod_comments' . $langId)->getCaption(); ?></label>
                                                    </div>
                                                    <div class="field-wraper">
                                                        <div class="field_cover"><?php echo $frmSellerProduct->getFieldHtml('selprod_comments' . $langId); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="field-set">
                                                    <div class="caption-wraper"><label class="field_label"><?php echo $frmSellerProduct->getField('selprod_rental_terms' . $langId)->getCaption(); ?></label>
                                                    </div>
                                                    <div class="field-wraper">
                                                        <div class="field_cover"><?php echo $frmSellerProduct->getFieldHtml('selprod_rental_terms' . $langId); ?>
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
                </div>
            <?php } ?>
            <div class="row">
                <div class="col-6">
                    <div class="field-set">
                        <div class="caption-wraper"><label class="field_label"></label></div>
                        <div class="field-wraper">
                            <div class="field_cover">
                                <?php echo $frmSellerProduct->getFieldHtml('btn_cancel'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 text-right">
                    <div class="field-set">
                        <div class="caption-wraper"><label class="field_label"></label></div>
                        <div class="field-wraper">
                            <div class="field_cover">
                                <?php echo $frmSellerProduct->getFieldHtml('btn_submit'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            echo $frmSellerProduct->getFieldHtml('selprod_product_id');
            echo $frmSellerProduct->getFieldHtml('selprod_urlrewrite_id');
            echo $frmSellerProduct->getFieldHtml('selprod_id');
            ?>
            </form>
            <?php echo $frmSellerProduct->getExternalJS(); ?>
        </div>
    </div>
</div>
<?php echo FatUtility::createHiddenFormFromData(array('product_id' => $product_id), array('name' => 'frmSearchSellerProducts')); ?>
<script type="text/javascript">
    $('[data-toggle="tooltip"]').tooltip();
    var PERCENTAGE = <?php echo applicationConstants::PERCENTAGE; ?>;
    var FLAT = <?php echo applicationConstants::FLAT; ?>;
    var CONF_PRODUCT_SKU_MANDATORY = <?php echo FatApp::getConfig("CONF_PRODUCT_SKU_MANDATORY", FatUtility::VAR_INT, 1); ?>;
    var LBL_MANDATORY_OPTION_FIELDS = '<?php echo Labels::getLabel('LBL_Atleast_one_option_needs_to_be_added_before_creating_inventory_for_this_product', $siteLangId); ?>';

    $("document").ready(function () {
        addMoreOptionRow = function () {
            var rowHtml = $('#optionsTable-js tr.formFields--js:first').html();
            var rowCount = parseInt($('#optionsTable-js tr.formFields--js').length);
            var totalVariants = parseInt($('input[name="variantOptionCount"]').val());
            if (rowCount == totalVariants) {
                $.mbsmessage('<?php echo Labels::getLabel('LBL_You_can_not_add_rows_more_then_available_variants', $siteLangId); ?>', false, 'alert--danger');
                return;
            }
            $('#optionsTable-js tr.formFields--js:last').after('<tr class="formFields--js">' + rowHtml + '</tr>');
            $('#optionsTable-js tr:last .action-btn--js').find('.btn-add-row--js').remove();
            var removeRowBtn = '<button title="<?php echo Labels::getLabel('LBL_Remove_Option', $siteLangId); ?>" onClick="removeOptionRow(this)" type="button" class="btn btn-secondary  btn-icon btn-remove-row--js"> <i class="icn"><svg class="svg" width="16px" height="16px"><use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#minus"></use></svg></i></button>';
            $('#optionsTable-js tr:last .action-btn--js').html(removeRowBtn);
        };

        removeOptionRow = function (el) {
            $(el).parents('.formFields--js').remove();
        };

        updateFieldNames = function (el) {
            var optionId = $(el).val();
            var index = 0;
            var rowIndex = $(el).parents('tr').rowIndex;
            var count = '<?php echo SellerProduct::UPDATE_OPTIONS_COUNT; ?>';
            if (parseInt(count) < rowIndex) {
                index++;
            }
            if (optionId != '' && optionId != undefined) {
                $(el).parents('tr').find("#varient_id--js").attr('name', 'varients[' + index + '][variantid' + optionId + ']');
                $(el).parents('tr').find("#securityFld--js").attr('name', 'varients[' + index + '][sprodata_rental_security' + optionId + ']');
                $(el).parents('tr').find("#bufferDaysFld--js").attr('name', 'varients[' + index + '][sprodata_rental_buffer_days' + optionId + ']');
                $(el).parents('tr').find("#selprodCostFld--js").attr('name', 'varients[' + index + '][selprod_cost' + optionId + ']');
                $(el).parents('tr').find("#rentalPriceFld--js").attr('name', 'varients[' + index + '][sprodata_rental_price' + optionId + ']');
                $(el).parents('tr').find("#rentalStockFld--js").attr('name', 'varients[' + index + '][sprodata_rental_stock' + optionId + ']');
            }
        };
        
        $(document).on('keyup', ".optionFld-js input", function() {
            var currentObj = $(this);
            var showCopyBtn = true;
            if (currentObj.val().length > 0) {
                currentObj.parent().parent().find('input').each(function() {
                    if ($(this).parent().hasClass('fldSku') && CONF_PRODUCT_SKU_MANDATORY !=
                        1) {
                        return;
                    }
                    if ($(this).val().length == 0 || $(this).val() == 0) {
                        $(this).attr('class', 'error');
                        showCopyBtn = false;
                    }
                });
                currentObj.removeClass('error');
            } else {
                var allEmpty = true;
                currentObj.parent().parent().find('input').each(function() {
                    if ($(this).val().length > 0) {
                        allEmpty = false;
                    }
                });
                if (allEmpty) {
                    currentObj.parent().parent().find('input').each(function() {
                        $(this).removeClass('error');
                        showCopyBtn = false;
                    });
                } else {
                    currentObj.attr('class', 'error');
                    showCopyBtn = false;
                }
            }

            if (showCopyBtn == true) {
                currentObj.parent().parent().find('button').removeAttr("disabled");;
            } else {
                currentObj.parent().parent().find('button').attr("disabled", "disabled");;
            }

        });
        
        copyRowData = function(btn) {
            var copiedData = '';
            $(btn).parent().parent().find('input').each(function() {
                copiedData = copiedData + $(this).val() + '\t';
            });

            var copiedField = document.createElement('input');
            copiedField.value = copiedData;
            document.body.appendChild(copiedField)
            copiedField.select();
            document.execCommand("copy", false);
            copiedField.remove();

            $(btn).attr('title', langLbl.copied);
            $(btn).addClass('clicked');
        }
        
        
    });

    $(document).on('paste', '.optionFld-js input', function (e) {
        if ($('.copy-btn--js').length > 1) {
            e.preventDefault();
            var pastedData = e.originalEvent.clipboardData.getData('text');
            var pastedDataArr = pastedData.split('\t');
            var count = 0;
            $(this).parent().parent().find('input').each(function () {
                $(this).val('')
                $(this).val(pastedDataArr[count])
                count = parseInt(count) + 1;
            });
            $(this).parent().parent().find('button').removeAttr("disabled");
            $('.js-copy-btn').attr('title', langLbl.copyToClipboard);
            $('.js-copy-btn').removeClass('clicked');
            $(this).parent().parent().next().children().children().first().focus();
        }
    });
</script>