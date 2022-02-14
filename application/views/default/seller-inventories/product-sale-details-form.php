<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$cancellationAgeFld = $frm->getField('selprod_cancellation_age');
$hidden = '';
if ('' === $cancellationAgeFld->value) {
    $hidden = 'hidden';
}

$fld = $frm->getField('selprod_subtract_stock');
$fld->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
$fld->developerTags['cbHtmlAfterCheckbox'] = '';

$fld = $frm->getField('selprod_track_inventory');
$fld->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
$fld->developerTags['cbHtmlAfterCheckbox'] = '';

$fld = $frm->getField('use_shop_policy');
$fld->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
$fld->developerTags['cbHtmlAfterCheckbox'] = '';
?>

<section>
    <div class="sectionbody space">
        <div class="tabs_nav_container  flat">
            <div class="tabs_panel_wrap">
                <div class="tabs_panel_wrap">
                    <?php
                    $frm->setFormTagAttribute('onsubmit', 'setupProductSaleDetails(this); return(false);');
                    $frm->setFormTagAttribute('class', 'form form--horizontal layout--ltr');
                    $frm->developerTags['colClassPrefix'] = 'col-md-';
                    $frm->developerTags['fld_default_col'] = 12;
                    echo $frm->getFormTag();
                    ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="field-set d-flex align-items-center">
                                <div class="field-wraper">
                                    <div class="field_cover">
                                        <?php echo $frm->getFieldHtml('selprod_subtract_stock'); ?>
                                        <label class="field_label"> <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title='<?php echo Labels::getLabel('LBL_track_stock_field_tooltip', $siteLangId); ?>'></i> </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-set d-flex align-items-center mb-0">
                                <div class="field-wraper">
                                    <div class="field_cover">
                                        <?php echo $frm->getFieldHtml('selprod_track_inventory'); ?>
                                        <label class="field_label"> <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title='<?php echo Labels::getLabel('LBL_track_stock_field_tooltip', $siteLangId); ?>'></i> </label>
                                    </div>
                                </div>
                                
                            </div>
                            <small class="note mt-0"><?php echo Labels::getLabel('LBL_This_Setting_will_work_after_enable_System_Subtract_Stock_Setting', $siteLangId); ?></small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="selprod_threshold_stock_level_fld col-md-6">
                            <div class="field-set">
                                <div class="caption-wraper"><label class="field_label">
                                        <?php echo $frm->getField('selprod_threshold_stock_level')->getCaption(); ?>
                                        <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right"
                                           title="<?php echo Labels::getLabel('LBL_Alert_stock_level_hint_info', $siteLangId); ?>"></i></label>
                                </div>
                                <div class="field-wraper">
                                    <div class="field_cover"><?php echo $frm->getFieldHtml('selprod_threshold_stock_level'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-set">
                                <div class="caption-wraper"><label class="field_label"><?php echo $frm->getField('selprod_min_order_qty')->getCaption(); ?><span
                                            class="spn_must_field">*</span></label></div>
                                <div class="field-wraper">
                                    <div class="field_cover"><?php echo $frm->getFieldHtml('selprod_min_order_qty'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="field-set">
                                <div class="caption-wraper"><label class="field_label"><?php echo $frm->getField('selprod_active')->getCaption(); ?></label>
                                </div>
                                <div class="field-wraper">
                                    <div class="field_cover"><?php echo $frm->getFieldHtml('selprod_active'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-set">
                                <div class="caption-wraper"><label class="field_label"><?php echo $frm->getField('selprod_available_from')->getCaption(); ?><span
                                            class="spn_must_field">*</span><i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right"
                                           title="<?php echo Labels::getLabel('LBL_Select_the_date_from_which_the_current_inventory_can_be_available.', $siteLangId); ?>"></i></label></div>
                                <div class="field-wraper">
                                    <div class="field_cover"><?php echo $frm->getFieldHtml('selprod_available_from'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="field-set">
                                <div class="caption-wraper"><label class="field_label"><?php echo $frm->getField('selprod_condition')->getCaption(); ?><span
                                            class="spn_must_field">*</span></label></div>
                                <div class="field-wraper">
                                    <div class="field_cover"><?php echo $frm->getFieldHtml('selprod_condition'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-set">
                                <div class="caption-wraper"><label class="field_label"></label></div>
                                <div class="field-wraper">
                                    <div class="field_cover"><?php echo $frm->getFieldHtml('use_shop_policy'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row use-shop-policy <?php echo $hidden; ?>">
                        <div class="col-md-6">
                            <div class="field-set">
                                <div class="caption-wraper"><label class="field_label"><?php echo $frm->getField('selprod_return_age')->getCaption(); ?></label>
                                </div>
                                <div class="field-wraper">
                                    <div class="field_cover"><?php echo $frm->getFieldHtml('selprod_return_age'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-set">
                                <div class="caption-wraper"><label class="field_label"><?php echo $frm->getField('selprod_cancellation_age')->getCaption(); ?></label>
                                </div>
                                <div class="field-wraper">
                                    <div class="field_cover"><?php echo $frm->getFieldHtml('selprod_cancellation_age'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <table id="optionsTable-js" class="table scroll-hint">
                                <thead>
                                    <tr>
                                        <?php if(!empty($optionCombinations)) { ?>
                                        <th><?php echo Labels::getLabel('LBL_Variant/option', $siteLangId); ?></th>
                                        <?php } ?>
                                        <?php /* <th width="10%"><?php echo Labels::getLabel('LBL_Cost_Price', $siteLangId); ?></th> */ ?>
                                        <th><?php echo Labels::getLabel('LBL_Selling_Price', $siteLangId); ?>
                                            <?php if(FatApp::getConfig('CONF_PRODUCT_INCLUSIVE_TAX', FatUtility::VAR_INT, 0)) {
                                                echo "<span class=''>(". Labels::getLabel('LBL_Including_Tax', $siteLangId)  .")</span>";
                                            } ?>
                                        </th>
                                        <th><?php echo Labels::getLabel('LBL_Quantity', $siteLangId); ?></th>
                                        <th><?php echo Labels::getLabel('LBL_SKU', $siteLangId); ?> <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right"
                                           title="<?php echo Labels::getLabel('LBL_SKU(Stock_Keeping_Unit)_is_a_unique_code_provided_to_each_product.', $siteLangId); ?>"></i></th>
                                        <?php if (count($selprodListing) > 1) { ?>
                                            <th></th>
                                        <?php } ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($selprodListing as $key => $selproduct) { ?>
                                        <tr>
                                            <?php if(!empty($optionCombinations)) { ?>
                                            <td>
                                                <?php
                                                $replaceCode = $selproduct['product_id'] . '_';
                                                $selProdCode = str_replace($replaceCode, "", $selproduct['selprod_code']);
                                                echo (isset($optionCombinations[$selProdCode])) ? $optionCombinations[$selProdCode] : "";
                                                ?>
                                            </td>
                                            <?php } /* <td><?php echo $frm->getFieldHtml('selprod_cost[' . $key . ']'); ?></td> */ ?>
                                            <td class="optionFld-js"><?php echo $frm->getFieldHtml('selprod_price[' . $key . ']'); ?></td>
                                            <td class="optionFld-js"><?php echo $frm->getFieldHtml('selprod_stock[' . $key . ']'); ?></td>
                                            <td class="optionFld-js"><?php echo $frm->getFieldHtml('selprod_sku[' . $key . ']'); ?></td>
                                            <?php if (count($selprodListing) > 1) { ?>
                                                <!-- COPY OPTION DATA -->
                                                <td>
                                                    <button onClick="copyRowData(this)" type="button" class="js-copy-btn btn btn-secondary btn-elevate btn-icon" title="<?php echo Labels::getLabel('LBL_Copy_to_clipboard', $siteLangId) ?>">
                                                        <i class="fas fa-paste"></i>
                                                    </button>
                                                </td>
                                                <!--  -->
                                            <?php } ?>
                                        </tr>
                                        <?php
                                        echo $frm->getFieldHtml('selprod_id[' . $key . ']');
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="field-set">
                                <div class="caption-wraper"><label class="field_label"></label></div>
                                <div class="field-wraper">
                                    <div class="field_cover">
                                        <?php
                                        $btnFld = $frm->getField('btn_submit');
                                        $btnFld->setFieldTagAttribute('class', 'btn btn-brand');
                                        echo $frm->getFieldHtml('btn_submit');
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </form>
                    <?php echo $frm->getExternalJS(); ?>
                </div>
            </div>
        </div>
    </div>
</section>


<script type="text/javascript">
    $('[data-toggle="tooltip"]').tooltip();
    $("document").ready(function () {
        $("input[name='selprod_track_inventory']").change(function () {
            if ($(this).prop("checked") == false) {
                $("input[name='selprod_threshold_stock_level']").val(0);
                $("input[name='selprod_threshold_stock_level']").attr("disabled", "disabled");
            } else {
                $("input[name='selprod_threshold_stock_level']").removeAttr("disabled");
            }
        });

        $("input[name='selprod_track_inventory']").trigger('change');
        $("input[name='use_shop_policy']").change(function () {
            if ($(this).is(":checked")) {
                $('.use-shop-policy').addClass('hidden');
            } else {
                $('.use-shop-policy').removeClass('hidden');
            }
        });
      });    
        /* [ COPY INPUT DATA FUNCTION GOES HERE */
        $("document").ready(function () {
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
        /* ] */
        
        
  
</script>