<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$productFrm->setFormTagAttribute('class', 'form form--horizontal');
$productFrm->setFormTagAttribute('onsubmit', 'setUpProductShipping(this); return(false);');
$productFrm->developerTags['colClassPrefix'] = 'col-md-';
$productFrm->developerTags['fld_default_col'] = 6;

$codEnabledFld = $productFrm->getField('product_cod_enabled');
if (null != $codEnabledFld) {
    $codEnabledFld->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
}

$btnBackFld = $productFrm->getField('btn_back');
$btnBackFld->setFieldTagAttribute('onClick', 'productOptionsAndTag(' . $productId . ')');
$btnBackFld->setFieldTagAttribute('class', "btn btn-outline-brand");

$btnSubmitFld = $productFrm->getField('btn_submit');
$btnSubmitFld->setWrapperAttribute('class', 'text-right');
$btnSubmitFld->setFieldTagAttribute('class', "btn btn-brand");
?>
<div class="row justify-content-center">
    <div class="col-md-12">
        <?php echo $productFrm->getFormTag(); ?>
        <div class="row">
            <?php
            $dimenEnabled = FatApp::getConfig("CONF_PRODUCT_DIMENSIONS_ENABLE", FatUtility::VAR_INT, 1);
            $shippedByAdminOnly = FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0);
            if (!$shippedByAdminOnly) {
                ?>
                <div class="col-md-<?php echo (1 > $dimenEnabled ? '12' : '6'); ?>">
                    <div class="field-set">
                        <div class="caption-wraper">
                            <label class="field_label">
                                <?php
                                $fld = $productFrm->getField('shipping_profile');
                                echo $fld->getCaption();
                                ?>
                                <span class="spn_must_field">*</span>
                            </label>
                        </div>
                        <div class="field-wraper">
                            <div class="field_cover">
                                <?php echo $productFrm->getFieldHtml('shipping_profile'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <?php if (FatApp::getConfig("CONF_PRODUCT_DIMENSIONS_ENABLE", FatUtility::VAR_INT, 0)) { ?>
                <div class="col-md-<?php echo (1 > $shippedByAdminOnly ? '6' : '12'); ?>">
                    <div class="field-set">
                        <div class="caption-wraper d-flex justify-content-between">
                            <label class="field_label">
                                <?php
                                $fld = $productFrm->getField('product_ship_package');
                                echo $fld->getCaption();
                                ?>
                                <span class="spn_must_field">*</span>
                            </label>
                            <?php if (!FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0)) { ?>
                                <small><a class="form-text text-muted" href="javascript:void(0)" onClick="shippingPackages()"><?php echo Labels::getLabel('LBL_Shipping_Packages', $siteLangId); ?></a></small>
                            <?php } ?>
                        </div>
                        <div class="field-wraper">
                            <div class="field_cover">
                                <?php echo $productFrm->getFieldHtml('product_ship_package'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="field-set">
                    <div class="caption-wraper">
                        <label class="field_label">
                            <?php
                            $fld = $productFrm->getField('product_weight_unit');
                            echo $fld->getCaption();
                            ?>
                            <span class="spn_must_field">*</span>
                        </label>
                    </div>
                    <div class="field-wraper">
                        <div class="field_cover">
                            <?php echo $productFrm->getFieldHtml('product_weight_unit'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="field-set">
                    <div class="caption-wraper">
                        <label class="field_label">
                            <?php
                            $fld = $productFrm->getField('product_weight');
                            echo $fld->getCaption();
                            ?>
                            <span class="spn_must_field">*</span>
                        </label>
                    </div>
                    <div class="field-wraper">
                        <div class="field_cover">
                            <?php echo $productFrm->getFieldHtml('product_weight'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <?php
            $codFld = $productFrm->getField('product_cod_enabled');
            if (!FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0) && !empty($codFld)) {
                ?>
                <div class="col-md-6">
                    <div class="field-set">
                        <div class="caption-wraper">&nbsp;</div>
                        <div class="field-wraper">
                            <div class="field_cover">
                                <?php echo $productFrm->getFieldHtml('product_cod_enabled'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="field-set">
                    <div class="caption-wraper"><label class="field_label"></label></div>
                    <div class="field-wraper">
                        <div class="field_cover"><?php echo $productFrm->getFieldHtml('btn_back'); ?></div>
                    </div>
                </div>
            </div>
            <div class="text-right col-md-6">
                <div class="field-set">
                    <div class="caption-wraper"><label class="field_label"></label></div>
                    <div class="field-wraper">
                        <div class="field_cover"><?php echo $productFrm->getFieldHtml('btn_submit'); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        echo $productFrm->getFieldHtml('product_id');
        echo $productFrm->getFieldHtml('preq_id');
        ?>
        </form>
        <?php echo $productFrm->getExternalJS(); ?>
    </div>
</div>