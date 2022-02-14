<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="row justify-content-center">
    <div class="col-md-12">


        <?php
        $frmSellerProduct->setFormTagAttribute('onsubmit', 'setUpSellerProduct(this); return(false);');
        $frmSellerProduct->setFormTagAttribute('class', 'web_form');
        $frmSellerProduct->developerTags['colClassPrefix'] = 'col-md-';
        $frmSellerProduct->developerTags['fld_default_col'] = 12;

        $idFld = $frmSellerProduct->getField('selprod_id');
        $idFld->setFieldTagAttribute('id', 'selprod_id');
        $shopUserNameFld = $frmSellerProduct->getField('selprod_user_shop_name');
        $shopUserNameFld->setfieldTagAttribute('readonly', 'readonly');
        $urlFld = $frmSellerProduct->getField('selprod_url_keyword');
        $urlFld->htmlAfterField = "<small class='text--small'>" . UrlHelper::generateFullUrl('Products', 'View', array($selprod_id), CONF_WEBROOT_FRONT_URL) . '</small>';
        /* $urlFld->setFieldTagAttribute('onkeyup', "getSlugUrl(this,this.value,$selprod_id,'post')"); */
        $urlFld->setFieldTagAttribute('onkeyup', "getUniqueSlugUrl(this,this.value,$selprod_id)");
        $urlFld->requirements()->setRequired();
        //$urlFld->addFieldTagAttribute('class', 'hide');
        //$selprodCodEnabledFld = $frmSellerProduct->getField('selprod_cod_enabled');
        //$selprodCodEnabledFld->setWrapperAttribute('class', 'selprod_cod_enabled_fld');


        $fld = $frmSellerProduct->getField('selprod_enable_rfq');
        if (!empty($fld)) {
            $fld->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
            $fld->developerTags['cbHtmlAfterCheckbox'] = '';
        }

        $autoUpdateFld = $frmSellerProduct->getField('auto_update_other_langs_data');
        if (null != $autoUpdateFld) {
            $autoUpdateFld->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
            $autoUpdateFld->developerTags['cbHtmlAfterCheckbox'] = '';
        }

        ?>
        <?php echo $frmSellerProduct->getFormTag(); ?>
        <div class="row">
            <div class="col-md-6">
                <div class="field-set">
                    <div class="caption-wraper"><label class="field_label"><?php echo $frmSellerProduct->getField('selprod_title' . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1))->getCaption(); ?><span class="spn_must_field">*</span></label></div>
                    <div class="field-wraper">
                        <div class="field_cover"><?php echo $frmSellerProduct->getFieldHtml('selprod_title' . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1)); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="field-set">
                    <div class="caption-wraper"><label class="field_label"><?php echo $frmSellerProduct->getField('selprod_user_shop_name')->getCaption(); ?><span class="spn_must_field">*</span></label></div>
                    <div class="field-wraper">
                        <div class="field_cover"><?php echo $frmSellerProduct->getFieldHtml('selprod_user_shop_name'); ?></div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="field-set">
                    <div class="caption-wraper"><label class="field_label"><?php echo $frmSellerProduct->getField('selprod_url_keyword')->getCaption(); ?><span class="spn_must_field">*</span></label></div>
                    <div class="field-wraper">
                        <div class="field_cover"><?php echo $frmSellerProduct->getFieldHtml('selprod_url_keyword'); ?></div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="field-set">
                    <div class="caption-wraper"><label class="field_label"><?php echo $frmSellerProduct->getField('sprodata_minimum_rental_quantity')->getCaption(); ?><span class="spn_must_field">*</span></label></div>
                    <div class="field-wraper">
                        <div class="field_cover"><?php echo $frmSellerProduct->getFieldHtml('sprodata_minimum_rental_quantity'); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="field-set">
                    <div class="caption-wraper"><label class="field_label"><?php echo $frmSellerProduct->getField('sprodata_rental_active')->getCaption(); ?></label></div>
                    <div class="field-wraper">
                        <div class="field_cover"><?php echo $frmSellerProduct->getFieldHtml('sprodata_rental_active'); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="field-set">
                    <div class="caption-wraper"><label class="field_label"><?php echo $frmSellerProduct->getField('sprodata_rental_available_from')->getCaption(); ?><span class="spn_must_field">*</span></label></div>
                    <div class="field-wraper">
                        <div class="field_cover"><?php echo $frmSellerProduct->getFieldHtml('sprodata_rental_available_from'); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="field-set">
                    <div class="caption-wraper"><label class="field_label"><?php echo $frmSellerProduct->getField('sprodata_rental_condition')->getCaption(); ?><span class="spn_must_field">*</span></label></div>
                    <div class="field-wraper">
                        <div class="field_cover"><?php echo $frmSellerProduct->getFieldHtml('sprodata_rental_condition'); ?></div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="field-set">
                    <div class="caption-wraper"><label class="field_label"><?php echo $frmSellerProduct->getField('sprodata_duration_type')->getCaption(); ?></label></div>
                    <div class="field-wraper">
                        <div class="field_cover"><?php echo $frmSellerProduct->getFieldHtml('sprodata_duration_type'); ?></div>
                        <span class="note text-danger"><?php echo Labels::getLabel('LBL_Duration_discount_may_be_affected_after_duration_type_change', $adminLangId); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="field-set">
                    <div class="caption-wraper"><label class="field_label"><?php echo $frmSellerProduct->getField('sprodata_minimum_rental_duration')->getCaption(); ?></label></div>
                    <div class="field-wraper">
                        <div class="field_cover"><?php echo $frmSellerProduct->getFieldHtml('sprodata_minimum_rental_duration'); ?></div>
                    </div>
                </div>
            </div>

            <div class="selprod_fulfillment_type_fld col-md-6">
                <div class="field-set">
                    <div class="caption-wraper"><label class="field_label"><?php echo $frmSellerProduct->getField('selprod_fulfillment_type')->getCaption(); ?></label></div>
                    <div class="field-wraper">
                        <div class="field_cover"><?php echo $frmSellerProduct->getFieldHtml('selprod_fulfillment_type'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (FatApp::getConfig('CONF_ENABLE_RFQ_MODULE_WITH_PRODUCTS', FatUtility::VAR_INT, 0)) { ?>
            <div class="gap"></div>
            <div class="row">
                <div class="col-md-6">
                    <div class="field-set">
                        <div class="field-wraper">
                            <div class="field_cover"><?php echo $frmSellerProduct->getFieldHtml('selprod_enable_rfq'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="gap"></div>
        <?php } ?>

        <?php if (FatApp::getConfig('CONF_ALLOW_MEMBERSHIP_MODULE', FatUtility::VAR_INT, 0)) { ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="field-set">
                        <div class="caption-wraper"><label class="field_label"><?php echo $frmSellerProduct->getField('membership_plan')->getCaption(); ?></label></div>
                        <?php
                        $tagDataString = '';
                        if (!empty($memberShipPlans)) {
                            foreach ($memberShipPlans as $key => $data) {
                                $tagData[$key]['id'] = $data['spm_membership_id'];
                                $tagData[$key]['value'] = $data['spm_membership_id'];
                                $tagDataString = htmlspecialchars(json_encode($tagData), ENT_QUOTES, 'UTF-8');
                            }
                        }
                        ?>

                        <div class="field-wraper">
                            <input type="text" class="membership-plan" name="membership_plan" value="<?php echo $tagDataString; ?>" />
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>

        <div class="row">
            <div class="col-md-12">
                <table id="shipping" class="table table-bordered mb-4">
                    <thead>
                        <tr>
                            <?php if (!empty($optionValues)) { ?>
                                <th width="20%"><?php echo Labels::getLabel('LBL_Variant/Option', $adminLangId); ?></th>
                            <?php } ?>
                            <th width="20%"><?php echo Labels::getLabel('LBL_Original_Price', $adminLangId); ?></th>
                            <?php if (!FatApp::getConfig('CONF_ALLOW_MEMBERSHIP_MODULE', FatUtility::VAR_INT, 0)) { ?>
                                <th width="10%"><?php echo Labels::getLabel('LBL_Buffer_Days', $adminLangId); ?> <i class="tabs-icon fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="<?php echo Labels::getLabel('LBL_Buffer_Days_Detail', $adminLangId); ?>">
                                    </i> </th>
                                <th width="20%"><?php echo Labels::getLabel('LBL_Security_Amount', $adminLangId); ?> </th>
                                <th width="50%">
                                    <?php echo Labels::getLabel('LBL_Rental_Price', $adminLangId); ?>
                                    <?php if(FatApp::getConfig('CONF_PRODUCT_INCLUSIVE_TAX', FatUtility::VAR_INT, 0)) {
                                        echo "<span class=''>(". Labels::getLabel('LBL_Including_Tax', $adminLangId)  .")</span>";
                                    } ?>    
                                </th>
                            <?php } ?>
                            <th width="20%"><?php echo Labels::getLabel('LBL_Quantity', $adminLangId); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <?php if (!empty($optionValues)) { ?>
                                <td><?php echo implode(' | ', $optionValues); ?></td>
                            <?php } ?>
                            <td><?php echo $frmSellerProduct->getFieldHtml('selprod_cost'); ?></td>
                            <?php if (!FatApp::getConfig('CONF_ALLOW_MEMBERSHIP_MODULE', FatUtility::VAR_INT, 0)) { ?>
                                <td><?php echo $frmSellerProduct->getFieldHtml('sprodata_rental_buffer_days'); ?></td>
                                <td><?php echo $frmSellerProduct->getFieldHtml('sprodata_rental_security'); ?></td>
                                <td class="price-container">
                                    <div class="price-hourly--js">
                                        <?php echo $frmSellerProduct->getFieldHtml('sprodata_rental_price'); ?>
                                    </div>
                                </td>
                            <?php } ?>
                            <td><?php echo $frmSellerProduct->getFieldHtml('sprodata_rental_stock'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="field-set">
                    <div class="caption-wraper"><label class="field_label"><?php echo $frmSellerProduct->getField('selprod_comments' . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1))->getCaption(); ?></label></div>
                    <div class="field-wraper">
                        <div class="field_cover"><?php echo $frmSellerProduct->getFieldHtml('selprod_comments' . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1)); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="field-set">
                    <div class="caption-wraper"><label class="field_label"><?php echo $frmSellerProduct->getField('selprod_rental_terms' . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1))->getCaption(); ?></label></div>
                    <div class="field-wraper">
                        <div class="field_cover"><?php echo $frmSellerProduct->getFieldHtml('selprod_rental_terms' . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1)); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <?php
                $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
                $languages = Language::getAllNames();
                unset($languages[$siteDefaultLangId]);
                $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
                if (!empty($translatorSubscriptionKey) && count($languages) > 0) { ?>
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

                <?php foreach ($languages as $langId => $langName) { ?>
                    <div class="accordians_container accordians_container-categories <?php echo 'layout--' . Language::getLayoutDirection($langId); ?>">
                        <div class="accordian_panel">
                            <span class="accordian_title accordianhead accordian_title" onclick="translateData(this, '<?php echo $siteDefaultLangId; ?>', '<?php echo $langId; ?>')"><?php echo Labels::getLabel('LBL_Inventory_Data_for', $adminLangId) ?> <?php echo $langName; ?></span>
                            <div class="accordian_body accordiancontent p-0" style="display: none;">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="field-set">
                                            <div class="caption-wraper"><label class="field_label"><?php echo $frmSellerProduct->getField('selprod_title' . $langId)->getCaption(); ?></label></div>
                                            <div class="field-wraper">
                                                <div class="field_cover"><?php echo $frmSellerProduct->getFieldHtml('selprod_title' . $langId); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="field-set">
                                            <div class="caption-wraper"><label class="field_label"><?php echo $frmSellerProduct->getField('selprod_comments' . $langId)->getCaption(); ?></label></div>
                                            <div class="field-wraper">
                                                <div class="field_cover"><?php echo $frmSellerProduct->getFieldHtml('selprod_comments' . $langId); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="field-set">
                                            <div class="caption-wraper"><label class="field_label"><?php echo $frmSellerProduct->getField('selprod_rental_terms' . $langId)->getCaption(); ?></label></div>
                                            <div class="field-wraper">
                                                <div class="field_cover"><?php echo $frmSellerProduct->getFieldHtml('selprod_rental_terms' . $langId); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
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
        echo $frmSellerProduct->getFieldHtml('selprod_id');
        ?>
        </form>
        <?php echo $frmSellerProduct->getExternalJS(); ?>
    </div>
</div>


<script type="text/javascript">
    $('[data-toggle="tooltip"]').tooltip();
    $("document").ready(function() {
        var addedByAdmin = <?php echo $product_added_by_admin; ?>;
        var shippedBySeller = <?php echo $shippedBySeller; ?>;
        if (shippedBySeller == 0) {
            $(".selprod_cod_enabled_fld").hide();
        }
        var INVENTORY_TRACK = <?php echo Product::INVENTORY_TRACK; ?>;
        var INVENTORY_NOT_TRACK = <?php echo Product::INVENTORY_NOT_TRACK; ?>;
    });
    /* [ TAGIFY FOR MEMBERSHIP PLAN */
    $("document").ready(function() {
        getOptionsAutoComplete = function(e) {
            var keyword = e.detail.value;
            var listOptions = [];
            fcom.ajax(fcom.makeUrl('SellerProducts', 'autoCompleteMembershipPlans'), {
                keyword: keyword
            }, function(t) {
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
        }).on('input', getOptionsAutoComplete);
        /* ] */
    });
</script>