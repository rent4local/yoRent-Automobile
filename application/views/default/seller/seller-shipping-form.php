<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="box__head">
    <h4><?php echo $productDetails['product_name']; ?>
    </h4>
</div>
<?php
$shippingFrm->setFormTagAttribute('class', 'form ');
$shippingFrm->setFormTagAttribute('onsubmit', 'setupSellerShipping(this); return(false);');

$shippingFrm->developerTags['colClassPrefix'] = 'col-md-';
$shippingFrm->developerTags['fld_default_col'] = 12;

$spProfileFld = $shippingFrm->getField('shipping_profile');
$spProfileFld->developerTags['col'] = 4;

$spPackageFld = $shippingFrm->getField('product_ship_package');
/* if (null != $spPackageFld) {
  $spPackageFld->developerTags['col'] = 4;
  } */

$psFreeFld = $shippingFrm->getField('ps_free');
/* if (null != $psFreeFld) {
  $psFreeFld->developerTags['col'] = 4;
  } */

$submitFld = $shippingFrm->getField('btn_submit');
if (null != $submitFld) {
    /* $submitFld->developerTags['col'] = 2;
      $submitFld->setWrapperAttribute('class', 'col-6'); */
    $submitFld->setFieldTagAttribute('class', 'btn btn-brand btn-block');
}

$cancelFld = $shippingFrm->getField('btn_cancel');
$cancelFld->setFieldTagAttribute('onClick', 'searchCatalogProducts(document.frmSearchCatalogProduct)');
/* $cancelFld->developerTags['col'] = 2;
  $cancelFld->setWrapperAttribute('class', 'col-6'); */
$cancelFld->setFieldTagAttribute('class', 'btn btn-outline-brand btn-block');
//$submitFld->attachField($cancelFld);
//echo $shippingFrm->getFormHTML();
?>
<?php echo $shippingFrm->getFormTag(); ?>
<div class="row">
    <div class="col-md-12">
        <div class="field-set">
            <div class="caption-wraper"><label class="field_label"><?php echo $shippingFrm->getField('shipping_profile')->getCaption(); ?><span class="spn_must_field">*</span></label></div>
            <div class="field-wraper">
                <div class="field_cover"><?php echo $shippingFrm->getFieldHtml('shipping_profile'); ?>
                </div>
            </div>
        </div>
    </div>
    <?php if (!empty($spPackageFld)) { ?>
        <div class="col-md-6">
            <div class="field-set">
                <div class="caption-wraper"><label class="field_label"><?php echo $shippingFrm->getField('product_ship_package')->getCaption(); ?><span class="spn_must_field">*</span></label></div>
                <div class="field-wraper">
                    <div class="field_cover"><?php echo $shippingFrm->getFieldHtml('product_ship_package'); ?></div>
                </div>
            </div>
        </div>
    <?php } ?>
    <?php if (!empty($psFreeFld)) { ?>
        <div class="col-md-4">
            <div class="field-set mb-0">
                <div class="caption-wraper"></div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <?php echo $shippingFrm->getFieldHtml('ps_free'); ?>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
    <div class="col-sm-12 mt-3">
        <h6><?php echo Labels::getLabel('LBL_Fullfilment_Setting_For_Sale', $siteLangId); ?></h6>
    </div>
    
    
    <?php if (empty($productInventories)) { ?>
        <div class="col-md-6">
            <div class="field-set">
                <div class="caption-wraper"><label class="field_label"><?php echo $shippingFrm->getField('fulfillment_method')->getCaption(); ?><span class="spn_must_field">*</span></label></div>
                <div class="field-wraper">
                    <div class="field_cover"><?php echo $shippingFrm->getFieldHtml('fulfillment_method'); ?></div>
                </div>
            </div>
        </div>
        <?php
    } else {
        echo '<div class="col-sm-12"><table width="100%" class="table table-justified" celspacing="20">'
        . '<tr>'
        . '<th>' . Labels::getLabel('LBL_Seller_Product_Name', $siteLangId) . '</th>'
        . '<th>' . Labels::getLabel('LBL_Fullfilment_method', $siteLangId) . '</th>'
        . '</tr>';
        foreach ($productInventories as $selprod) {
            $fld = $shippingFrm->getField('fulfillment_method[' . $selprod['selprod_id'] . ']');
            if (!empty($fld)) {
                ?>
                <tr>
                    <td>
                        <?php
                        echo $selprod['selprod_title'];
                        if (!empty($availableOptions) && isset($availableOptions[$selprod['selprod_code']])) {
                            echo ' - ' . $availableOptions[$selprod['selprod_code']];
                        }
                        ?>
                    </td>
                    <td>
                        <div class="field-set">
                            <div class="field-wraper">
                                <div class="field_cover"><?php echo $shippingFrm->getFieldHtml('fulfillment_method[' . $selprod['selprod_id'] . ']'); ?>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php
            }
        }
        echo '</table></div>';
    }
    ?>
    <div class="col-sm-2">
        <div class="field-set">
            <div class="caption-wraper"><label class="field_label"></label></div>
            <div class="field-wraper">
                <div class="field_cover">
                    <?php echo $shippingFrm->getFieldHtml('btn_submit'); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-2">
        <div class="field-set">
            <div class="caption-wraper"><label class="field_label"></label></div>
            <div class="field-wraper">
                <div class="field_cover">
                    <?php
                    echo $shippingFrm->getFieldHtml('ps_product_id');
                    echo $shippingFrm->getFieldHtml('btn_cancel');
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php ?>
</form>
<?php echo $shippingFrm->getExternalJS(); ?>


<script>
    var productOptions = [];
    var dv = $("#listing");
</script>