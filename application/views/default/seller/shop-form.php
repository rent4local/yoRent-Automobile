<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$shopFrm->setFormTagAttribute('onsubmit', 'setupShop(this); return(false);');
$shopFrm->setFormTagAttribute('class', 'form form--horizontal');

$shopFrm->developerTags['colClassPrefix'] = 'col-lg-4 col-md-';
$shopFrm->developerTags['fld_default_col'] = 4;

$countryFld = $shopFrm->getField('shop_country_code');
$countryFld->setFieldTagAttribute('id', 'shop_country_code');
$countryFld->setFieldTagAttribute('onChange', 'getStatesByCountryCode(this.value,' . $stateId . ',\'#shop_state\', \'state_code\')');
$countryFld->setFieldTagAttribute('class', 'addressSelection-js');
$stateFld = $shopFrm->getField('shop_state');
$stateFld->setFieldTagAttribute('id', 'shop_state');
$stateFld->setFieldTagAttribute('class', 'addressSelection-js');
$urlFld = $shopFrm->getField('urlrewrite_custom');
$urlFld->setFieldTagAttribute('id', "urlrewrite_custom");
/* $urlFld->setFieldTagAttribute('onkeyup', "getSlugUrl(this,this.value)"); */
$urlFld->setFieldTagAttribute('onkeyup', "getUniqueSlugUrl(this,this.value,$shop_id)");
$urlFld->htmlAfterField = "<p class='note' id='shopurl'>" . UrlHelper::generateFullUrl('Shops', 'View', array($shop_id), '/') . '</p>';
$IDFld = $shopFrm->getField('shop_id');
$IDFld->setFieldTagAttribute('id', "shop_id");
$identiFierFld = $shopFrm->getField('shop_identifier');
$identiFierFld->setFieldTagAttribute('onkeyup', "Slugify(this.value,'urlrewrite_custom','shop_id','shopurl')");
$variables = array('language' => $language, 'siteLangId' => $siteLangId, 'shop_id' => $shop_id, 'action' => $action);
$postalCode = $shopFrm->getField('shop_postalcode');
$postalCode->setFieldTagAttribute('id', "postal_code");

$latFld = $shopFrm->getField('shop_lat');
$latFld->setFieldTagAttribute('id', "lat");
$lngFld = $shopFrm->getField('shop_lng');
$lngFld->setFieldTagAttribute('id', "lng");

$fullfillFld = $shopFrm->getField('shop_fulfillment_type');
if (!empty($fullfillFld) && $shop_id > 0) {
    $fullfillFld->htmlAfterField = "<a href='javascript:void(0);' onClick='pickupAddress();'><small>" . Labels::getLabel('LBL_Click_here_to_manage_Pickup_Addresses', $siteLangId) . "</small></a>";
}

$slotFld = $shopFrm->getField('shop_pickup_interval');
if (!empty($slotFld)) {
    $slotFld->htmlAfterField = "<p class='note'>" . Labels::getLabel('LBL_For_Sale_Orders_Only', $siteLangId) . "</p>";
}

$btnSubmit = $shopFrm->getField('btn_submit');
/* $btnSubmit->developerTags['noCaptionTag'] = true; */
$btnSubmit->setFieldTagAttribute('class', "btn btn-brand btn-wide");

$variables = array('language' => $language, 'siteLangId' => $siteLangId, 'shop_id' => $shop_id, 'action' => $action);
$this->includeTemplate('seller/_partial/shop-navigation.php', $variables, false);

/* $roundOffFld = $shopFrm->getField('shop_enable_price_round_off');
$roundOffFld->setFieldTagAttribute('onChange', "showHideRoundoffTypeSelect(this.value);");

$roundOffTypeFld = $shopFrm->getField('shop_price_round_off_type');
$roundOffTypeFld->setWrapperAttribute('class', "round_off_type_container--js"); */
?>
<div class="tabs__content tabs__content-js">
    <div class="card">
        <div class="card-body ">
            <div class="row">
                <div class="col-lg-12 col-md-12" id="shopFormBlock"> <?php //echo $shopFrm->getFormHtml();   
                                                                        ?>


                    <div class="row">
                        <div class="col-md-12">
                            <div class="form__subcontent">
                                <?php echo $shopFrm->getFormTag(); ?>

                                <div class="row">
                                    <div class="col-lg-4 col-md-4">
                                        <div class="field-set">
                                            <div class="caption-wraper"><label class="field_label"><?php echo $shopFrm->getField('shop_identifier')->getCaption(); ?><span class="spn_must_field">*</span> <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="<?php echo Labels::getLabel('LBL_A_unique_identifier_key_that_represents_every_individual_seller.', $siteLangId); ?>"></i></label></div>
                                            <div class="field-wraper">
                                                <div class="field_cover"><?php echo $shopFrm->getFieldHtml('shop_identifier'); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-4">
                                        <div class="field-set">
                                            <div class="caption-wraper"><label class="field_label"><?php echo $shopFrm->getField('urlrewrite_custom')->getCaption(); ?><span class="spn_must_field">*</span> <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="<?php echo Labels::getLabel('LBL_Seo_url_tooltip_text', $siteLangId); ?>"></i></label></div>
                                            <div class="field-wraper">
                                                <div class="field_cover"><?php echo $shopFrm->getFieldHtml('urlrewrite_custom'); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-4">
                                        <div class="field-set">
                                            <div class="caption-wraper"><label class="field_label"><?php echo $shopFrm->getField('shop_phone')->getCaption(); ?><span class="spn_must_field">*</span></label></div>
                                            <div class="field-wraper">
                                                <div class="field_cover"><?php echo $shopFrm->getFieldHtml('shop_phone'); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-4 col-md-4">
                                        <div class="field-set">
                                            <div class="caption-wraper"><label class="field_label"><?php echo $shopFrm->getField('shop_country_code')->getCaption(); ?><span class="spn_must_field">*</span></label></div>
                                            <div class="field-wraper">
                                                <div class="field_cover"><?php echo $shopFrm->getFieldHtml('shop_country_code'); ?></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-4 col-md-4">
                                        <div class="field-set">
                                            <div class="caption-wraper"><label class="field_label"><?php echo $shopFrm->getField('shop_state')->getCaption(); ?><span class="spn_must_field">*</span></label></div>
                                            <div class="field-wraper">
                                                <div class="field_cover"><?php echo $shopFrm->getFieldHtml('shop_state'); ?></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-4 col-md-4">
                                        <div class="field-set">
                                            <div class="caption-wraper"><label class="field_label"><?php echo $shopFrm->getField('shop_postalcode')->getCaption(); ?></label></div>
                                            <div class="field-wraper">
                                                <div class="field_cover"><?php echo $shopFrm->getFieldHtml('shop_postalcode'); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-4 col-md-4">
                                        <div class="field-set">
                                            <div class="caption-wraper"><label class="field_label"><?php echo $shopFrm->getField('shop_supplier_display_status')->getCaption(); ?> <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="<?php echo Labels::getLabel('LBL_Display_status_tooltip_text', $siteLangId); ?>"></i></label></div>
                                            <div class="field-wraper">
                                                <div class="field_cover"><?php echo $shopFrm->getFieldHtml('shop_supplier_display_status'); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-4">
                                        <div class="field-set">
                                            <div class="caption-wraper"><label class="field_label"><?php echo $shopFrm->getField('shop_return_age')->getCaption(); ?> <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="<?php echo Labels::getLabel('LBL_Shop_return_age_tooltip_text', $siteLangId); ?>"></i></label></div>
                                            <div class="field-wraper">
                                                <div class="field_cover"><?php echo $shopFrm->getFieldHtml('shop_return_age'); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-4">
                                        <div class="field-set">
                                            <div class="caption-wraper"><label class="field_label"><?php echo $shopFrm->getField('shop_cancellation_age')->getCaption(); ?> <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="<?php echo Labels::getLabel('LBL_Shop_cancellation_age_tooltip_text', $siteLangId); ?>"></i></label></div>
                                            <div class="field-wraper">
                                                <div class="field_cover"><?php echo $shopFrm->getFieldHtml('shop_cancellation_age'); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-4 col-md-4">
                                        <div class="field-set">
                                            <div class="caption-wraper"><label class="field_label"><?php echo $shopFrm->getField('shop_pickup_interval')->getCaption(); ?> <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="<?php echo Labels::getLabel('LBL_Shop_pickup_interval_tooltip_text', $siteLangId); ?>"></i></label></div>
                                            <div class="field-wraper">
                                                <div class="field_cover"><?php echo $shopFrm->getFieldHtml('shop_pickup_interval'); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-4">
                                        <div class="field-set">
                                            <div class="caption-wraper"><label class="field_label"><?php echo $shopFrm->getField('shop_fulfillment_type')->getCaption(); ?></label></div>
                                            <div class="field-wraper">
                                                <div class="field_cover"><?php echo $shopFrm->getFieldHtml('shop_fulfillment_type'); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if (FatApp::getConfig('CONF_ENABLE_RENTAL_PRODUCT_LATE_CHARGES_MODULE', FatUtility::VAR_INT, 0)) { ?>
                                        <div class="col-lg-4 col-md-4">
                                            <div class="field-set">
                                                <div class="caption-wraper"><label class="field_label"><?php echo $shopFrm->getField('shop_is_enable_late_charges')->getCaption(); ?></label></div>
                                                <div class="field-wraper">
                                                    <div class="field_cover"><?php echo $shopFrm->getFieldHtml('shop_is_enable_late_charges'); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="row">
                                    <div class="col-lg-4 col-md-4">
                                        <div class="field-set">
                                            <div class="caption-wraper"><label class="field_label"><?php echo $shopFrm->getField('shop_invoice_codes')->getCaption(); ?> <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="<?php echo Labels::getLabel('LBL_Shop_invoice_codes_tooltip_text', $siteLangId); ?>"></i></label> </div>
                                            <div class="field-wraper">
                                                <div class="field_cover"><?php echo $shopFrm->getFieldHtml('shop_invoice_codes'); ?></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-4 col-md-4">
                                        <div class="field-set">
                                            <div class="caption-wraper">&nbsp;</div>
                                            <div class="field-wraper">
                                                <div class="field_cover"> <?php echo $shopFrm->getFieldHtml('shop_is_free_ship_active'); ?> <label class="field_label"> <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="<?php echo Labels::getLabel('LBL_Enable_free_shipping_if_the_order_exceeds_mentioned_value.', $siteLangId); ?>"></i> </label></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-4 col-md-4">
                                        <div class="field-set">
                                            <div class="caption-wraper"><label class="field_label"><?php echo $shopFrm->getField('shop_free_shipping_amount')->getCaption(); ?></label></div>
                                            <div class="field-wraper">
                                                <div class="field_cover"><?php echo $shopFrm->getFieldHtml('shop_free_shipping_amount'); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-4 col-md-4">
                                        <div class="field-set">
                                            <div class="caption-wraper"><label class="field_label"></label></div>
                                            <div class="field-wraper">
                                                <div class="field_cover"><?php echo $shopFrm->getFieldHtml('btn_submit'); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php echo $shopFrm->getFieldHtml('shop_id'); ?>
                                <?php echo $shopFrm->getFieldHtml('shop_lat'); ?>
                                <?php echo $shopFrm->getFieldHtml('shop_lng'); ?>
                                <?php echo $shopFrm->getFieldHtml('fatpostsectkn'); ?>



                                </form>
                                <?php echo $shopFrm->getExternalJS(); ?>
                            </div>
                        </div>
                    </div>

                    <?php if (trim(FatApp::getConfig('CONF_GOOGLEMAP_API_KEY', FatUtility::VAR_STRING, '')) != '') { ?>
                        <div class="col-lg-12 col-md-12" id="map" style="width:1500px; height:500px"></div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script language="javascript">
    <?php if (!$canEdit) { ?>
    $(document).ready(function() {
        $("form[name='frmShop'] input").prop("disabled", true);
        $("form[name='frmShop'] select").prop("disabled", true);
        $("form[name='frmShop'] textarea").prop("disabled", true);
    });
    <?php } ?>
</script>
<script>
    $(document).ready(function() {
        $('input[name="shop_is_free_ship_active"]').on('change', function() {
            if ($(this).prop('checked') == true) {
                $('input[name="shop_free_shipping_amount"]').removeAttr('disabled');
            } else {
                $('input[name="shop_free_shipping_amount"]').attr('disabled', 'disabled');
                $('input[name="shop_free_shipping_amount"]').val(0);
            }
        });
        $('input[name="shop_is_free_ship_active"]').trigger('change');
    });
</script>


<?php if (trim(FatApp::getConfig('CONF_ENABLE_GEO_LOCATION', FatUtility::VAR_STRING, '')) != '') { ?>
    <script>
        var lat = (!$('#lat').val()) ? 0 : $('#lat').val();
        var lng = (!$('#lng').val()) ? 0 : $('#lng').val();
        initMap(lat, lng);
    </script>
<?php } ?>
<script>
$(document).ready(function(){
	stylePhoneNumberFld("input[name='shop_phone']", false, 'shop_dial_code', 'shop_country_iso');
})
</script>
<?php
if (isset($countryIso) && !empty($countryIso)) { ?>
    <script>
        langLbl.defaultCountryCode = '<?php echo $countryIso; ?>';
    </script>
<?php } ?>
<style>
    .gm-style-mtc {
        display: none;
    }
</style>