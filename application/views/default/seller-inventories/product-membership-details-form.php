<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<section>
    <div class="sectionbody space">
        <div class="tabs_nav_container  flat">
            <div class="tabs_panel_wrap">
                <div class="tabs_panel_wrap">
                    <?php
                    if ($selprod_id > 0) {
                        $frm->setFormTagAttribute('onsubmit', 'setupProductMembershipDetails(this); return(false);');
                    } else {
                        $frm->setFormTagAttribute('onsubmit', 'setUpMultipleSelProdsMemberships(this); return(false);');
                    }
                    $frm->setFormTagAttribute('class', 'form form--horizontal layout--ltr');
                    $frm->developerTags['colClassPrefix'] = 'col-md-';
                    $frm->developerTags['fld_default_col'] = 12;

                    $cancellationAgeFld = $frm->getField('selprod_cancellation_age');
                    $hidden = '';
                    if ('' === $cancellationAgeFld->value) {
                        $hidden = 'hidden';
                    }

             

                    $fld = $frm->getField('use_shop_policy');
                    $fld->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
                    $fld->developerTags['cbHtmlAfterCheckbox'] = '';

                    $fld = $frm->getField('selprod_enable_rfq');
                    $fld->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
                    $fld->developerTags['cbHtmlAfterCheckbox'] = '';

                    echo $frm->getFormTag();
                    ?>
                    <!-- [ GENERAL FIELDS -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="field-set">
                                <div class="caption-wraper"><label class="field_label"><?php echo $frm->getField('sprodata_minimum_rental_quantity')->getCaption(); ?><span
                                            class="spn_must_field">*</span></label></div>
                                <div class="field-wraper">
                                    <div class="field_cover"><?php echo $frm->getFieldHtml('sprodata_minimum_rental_quantity'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="field-set">
                                <div class="caption-wraper"><label class="field_label"><?php echo $frm->getField('sprodata_rental_active')->getCaption(); ?></label>
                                </div>
                                <div class="field-wraper">
                                    <div class="field_cover"><?php echo $frm->getFieldHtml('sprodata_rental_active'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-set">
                                <div class="caption-wraper"><label class="field_label"><?php echo $frm->getField('sprodata_rental_available_from')->getCaption(); ?><span
                                            class="spn_must_field">*</span></label></div>
                                <div class="field-wraper">
                                    <div class="field_cover"><?php echo $frm->getFieldHtml('sprodata_rental_available_from'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="field-set">
                                <div class="caption-wraper"><label class="field_label"><?php echo $frm->getField('sprodata_rental_condition')->getCaption(); ?><span
                                            class="spn_must_field">*</span></label></div>
                                <div class="field-wraper">
                                    <div class="field_cover"><?php echo $frm->getFieldHtml('sprodata_rental_condition'); ?>
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
                        <div class="col-md-6">
                            <div class="field-set">
                                <div class="caption-wraper"><label class="field_label"><?php echo $frm->getField('membership_plan')->getCaption(); ?></label>
                                </div>
                                <div class="field-wraper">
                                    <div class="field_cover"><?php
                                        $tagDataString = '';
                                        if (!empty($membershipPlans)) {
                                            foreach ($membershipPlans as $key => $data) {
                                                $tagData[$key]['id'] = $data['spm_membership_id'];
                                                $tagData[$key]['value'] = $data['spm_membership_id'];
                                                $tagDataString = htmlspecialchars(json_encode($tagData), ENT_QUOTES, 'UTF-8');
                                            }
                                            //$fld->value = htmlspecialchars(json_encode($tagData), ENT_QUOTES, 'UTF-8');
                                        }
                                        //echo $frm->getFieldHtml('membership_plan');
                                        ?>
                                        <input type="text" name="membership_plan" value="<?php echo $tagDataString; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php if (empty($availableOptions) || $selprod_id > 0) { ?>
                            <div class="field-set">
                                <div class="caption-wraper"><label class="field_label"><?php echo $frm->getField('selprod_cost')->getCaption(); ?></label>
                                </div>
                                <div class="field-wraper">
                                    <div class="field_cover"><?php echo $frm->getFieldHtml('selprod_cost'); ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="col-md-6">
                            <div class="field-set">
                                <div class="caption-wraper"><label class="field_label"><?php echo $frm->getField('sprodata_duration_type')->getCaption(); ?></label>
                                </div>
                                <div class="field-wraper">
                                    <div class="field_cover"><?php echo $frm->getFieldHtml('sprodata_duration_type'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="field-set">
                                <div class="caption-wraper"><label class="field_label"><?php echo $frm->getField('sprodata_minimum_rental_duration')->getCaption(); ?></label>
                                </div>
                                <div class="field-wraper">
                                    <div class="field_cover"><?php echo $frm->getFieldHtml('sprodata_minimum_rental_duration'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="field-set">
                                <div class="caption-wraper"><label class="field_label"></label></div>
                                <div class="field-wraper">
                                    <div class="field_cover"><?php echo $frm->getFieldHtml('selprod_enable_rfq'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--  GENERAL FIELDS ] -->
                    <input type="hidden" name="variantOptionCount" value="<?php echo count($availableOptions); ?>">
                    <?php if (!empty($availableOptions) && 1 > $selprod_id) { ?>
                        <div class="row">
                            <div class="col-md-12">
                                <table id="optionsTable-js" class="table scroll-hint">
                                    <thead>
                                        <tr>
                                            <th width="10%"><?php echo Labels::getLabel('LBL_Variant/option', $siteLangId); ?></th>
                                            <th width="10%"><?php echo Labels::getLabel('LBL_Original_Price', $siteLangId); ?></th>
                                            <th width="10%"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="formFields--js">
                                            <td class="optionFld-js">
                                                <?php
                                                $optFld = $frm->getField('varient_id');
                                                $optFld->setFieldTagAttribute('onChange', 'updateFieldNames(this)');
                                                $optFld->setFieldTagAttribute('id', 'varient_id--js');
                                                echo $frm->getFieldHtml('varient_id');
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                $priceFld = $frm->getField('selprod_cost');
                                                $priceFld->setFieldTagAttribute('id', 'selprodCostFld--js');
                                                echo $frm->getFieldHtml('selprod_cost');
                                                ?>
                                            </td>
                                            <td class="action-btn--js">
                                                <button title="<?php echo Labels::getLabel('LBL_Add_Option', $siteLangId); ?>" onClick="addMoreOptionRow()" type="button" class="btn btn-secondary btn-icon btn-add-row--js">
                                                    <i class="icn">
                                    <svg class="svg" width="16px" height="16px">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#plus">
                                        </use>
                                    </svg>
                                </i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php } ?>
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
                                        echo $frm->getFieldHtml('selprod_product_id');
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
        $("input[name='use_shop_policy']").change(function () {
            if ($(this).is(":checked")) {
                $('.use-shop-policy').addClass('hidden');
            } else {
                $('.use-shop-policy').removeClass('hidden');
            }
        });

        /* [ TAGIFY FOR MEMBERSHIP PLAN */
        getMembershipPlanAutoComplete = function (e) {
            var keyword = e.detail.value;
            tagifyOption.loading(true).dropdown.hide.call(tagifyOption);
            var listOptions = [];
            fcom.ajax(fcom.makeUrl('sellerInventories', 'autoCompleteMembershipPlans'), '', function (t) {
                var ans = $.parseJSON(t);
                for (i = 0; i < ans.length; i++) {
                    listOptions.push({
                        "id": ans[i].id,
                        "value": ans[i].name + '(' + ans[i].plan_identifier + ')',
                    });
                }
                tagifyOption.settings.whitelist = listOptions;
                tagifyOption.loading(false).dropdown.show.call(tagifyOption, keyword);
            });
        };

        tagifyOption = new Tagify(document.querySelector('input[name=membership_plan]'), {
            whitelist: [],
            delimiters: "#",
            editTags: false,
        }).on('input', getMembershipPlanAutoComplete);
        /* ] */

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
            var removeRowBtn = '<button title="<?php echo Labels::getLabel('LBL_Remove_Option', $siteLangId); ?>" onClick="removeOptionRow(this)" type="button" class="btn btn-secondary  btn-icon btn-remove-row--js"> <i class="icn">
                                    <svg class="svg" width="16px" height="16px">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#minus">
                                        </use>
                                    </svg>
                                </i></button>';
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
                $(el).parents('tr').find("#selprodCostFld--js").attr('name', 'varients[' + index + '][selprod_cost' + optionId + ']');
            }
        };
    });
</script>