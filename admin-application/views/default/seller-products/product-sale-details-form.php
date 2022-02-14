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
<div class="row justify-content-center">
    <div class="col-md-12">
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
                                    <div class="field_cover"><?php echo $frm->getFieldHtml('selprod_subtract_stock'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-set d-flex align-items-center">
                                <div class="field-wraper">
                                    <div class="field_cover"><?php echo $frm->getFieldHtml('selprod_track_inventory'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="selprod_threshold_stock_level_fld col-md-6">
                            <div class="field-set">
                                <div class="caption-wraper"><label class="field_label">
                                        <?php echo $frm->getField('selprod_threshold_stock_level')->getCaption(); ?>
                                        <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right"
                                           title="<?php echo Labels::getLabel('LBL_Alert_stock_level_hint_info', $adminLangId); ?>"></i></label>
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
                                            class="spn_must_field">*</span></label></div>
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
                                        <?php /* <th width="10%"><?php echo Labels::getLabel('LBL_Cost_Price', $adminLangId); ?></th> */ ?>
                                        <th width="10%"><?php echo Labels::getLabel('LBL_Selling_Price', $adminLangId); ?>
                                            <?php if (FatApp::getConfig('CONF_PRODUCT_INCLUSIVE_TAX', FatUtility::VAR_INT, 0)) {
                                                echo "<span class=''>(". Labels::getLabel('LBL_Including_Tax', $adminLangId)  .")</span>";
                                            } ?>     
                                        </th>
                                        <th width="10%"><?php echo Labels::getLabel('LBL_Quantity', $adminLangId); ?></th>
                                        <th width="10%"><?php echo Labels::getLabel('LBL_SKU', $adminLangId); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <?php /* <td><?php echo $frm->getFieldHtml('selprod_cost'); ?></td> */ ?>
                                        <td><?php echo $frm->getFieldHtml('selprod_price'); ?></td>
                                        <td><?php echo $frm->getFieldHtml('selprod_stock'); ?></td>
                                        <td><?php echo $frm->getFieldHtml('selprod_sku'); ?></td>
                                    </tr>
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
                                        echo $frm->getFieldHtml('selprod_id');
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
</script>
<style>
    .hidden {display: none;}
</style>
