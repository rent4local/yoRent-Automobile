<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$durFld = $frm->getField('produr_rental_duration');
$durFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Minimum_Duration', $siteLangId));

$frm->setFormTagAttribute('class', 'form form--horizontal durationDiscountForm--js');
$frm->setFormTagAttribute('onsubmit', 'setUpSellerProductDurationDiscount(this); return(false);');

$durationAmtFld = $frm->getField('produr_discount_percent');
$durationAmtFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Percentage_(%)', $siteLangId)); 

$btnFld = $frm->getField('btn_submit');
$btnFld->setFieldTagAttribute('class', 'btn btn-brand');
?>
<div class="card-body">
    <div class="replaced">
        <?php
        echo $frm->getFormTag();
        echo $frm->getFieldHtml('produr_selprod_id');
        echo $frm->getFieldHtml('produr_id');
        ?>
        <div class="row">
            <div class="col-lg-3 col-md-3">
                <div class="field-set">
                    <div class="field-wraper">
                        <?php
                        if (0 >= $selprod_id) {
                            echo $frm->getFieldHtml('product_name');
                        } else {
                            echo SellerProduct::getProductDisplayTitle($selprod_id, $siteLangId, true);
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-4">
                <div class="field-set">
                    <div class="field-wraper">
                        <?php echo $frm->getFieldHtml('produr_rental_duration'); ?>
                        <span class="duration_label--js"><?php echo $durationLabel;?></span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-3">
                <div class="field-set">
                    <div class="field-wraper">
                        <?php echo $frm->getFieldHtml('produr_discount_percent'); ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-2">
                <div class="field-set">
                    <div class="field-wraper">
                        <div class="field_cover">
                            <?php  echo $frm->getFieldHtml('btn_submit'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </form>
        <?php echo $frm->getExternalJs(); ?>
    </div>
</div>
<div class="divider m-0"></div>