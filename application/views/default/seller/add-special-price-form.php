<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
    $selProdId = (!empty($data['splprice_selprod_id']) ? $data['splprice_selprod_id'] : 0);
    $frm = SellerProduct::specialPriceForm($siteLangId, $productFor);
    $prodName = $frm->addSelectBox(Labels::getLabel('LBL_Product', $siteLangId), 'product_name', [], '', array('class' => 'selProd--js','placeholder' => Labels::getLabel('LBL_Select_Product', $siteLangId)));
    //$prodName = $frm->addTextBox(Labels::getLabel('LBL_Product', $siteLangId), 'product_name', '', array('class' => 'selProd--js', 'placeholder' => Labels::getLabel('LBL_Select_Product', $siteLangId)));
    $prodName->requirements()->setRequired();

    $startDate = $frm->getField('splprice_start_date');
    $startDate->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Price_Start_Date', $siteLangId));
    $startDate->setFieldTagAttribute('class', 'date_js');
    $startDate->setFieldTagAttribute('disabled', 'disabled');

    $endDate = $frm->getField('splprice_end_date');
    $endDate->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Price_End_Date', $siteLangId));
    $endDate->setFieldTagAttribute('class', 'date_js');
    $endDate->setFieldTagAttribute('disabled', 'disabled');

    $splPrice = $frm->getField('splprice_price');
    $splPrice->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Special_Price', $siteLangId));
    $splPrice->setFieldTagAttribute('disabled', 'disabled');
    $splPrice->setFieldTagAttribute('class', 'js-special-price');
    
    $frm->setFormTagAttribute('class', 'form');
    $frm->setFormTagAttribute('onsubmit', 'updateSpecialPriceRow(this, ' . $selProdId . '); return(false);');
    $frm->addHiddenField('', 'addMultiple');

    $frm->setFormTagAttribute('id', 'frmAddSpecialPrice-' . $selProdId);
    $frm->setFormTagAttribute('name', 'frmAddSpecialPrice-' . $selProdId);

    $startDate = $frm->getField('splprice_start_date');
    $startDate->setFieldTagAttribute('id', 'splprice_start_date' . $selProdId);

    $endDate = $frm->getField('splprice_end_date');
    $endDate->setFieldTagAttribute('id', 'splprice_end_date' . $selProdId);

    $frm->addSubmitButton('', 'btn_update', Labels::getLabel('LBL_Save', $siteLangId), array('class' => 'btn btn-brand btn-block '));

if (!empty($data) && 0 < count($data)) {
    $data['product_name'] = isset($data['product_name']) ? html_entity_decode($data['product_name'], ENT_QUOTES, 'UTF-8') : '';
    $prodName->setFieldTagAttribute('readonly', 'readonly');
    $frm->fill($data);
}
?>
<div class="card-body">
    <div class="replaced">
        <?php
        echo $frm->getFormTag();
        echo $frm->getFieldHtml('splprice_selprod_id');
        echo $frm->getFieldHtml('addMultiple');
        echo $frm->getFieldHtml('product_for');
        ?>
            <div class="row">
                <div class="col-lg-3 col-md-3">
                    <div class="field-set">
                        <div class="field-wraper">
                            <?php echo $frm->getFieldHtml('product_name'); ?>
                            <div class="js-prod-price"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-2">
                    <div class="field-set">
                        <div class="field-wraper">
                            <?php echo $frm->getFieldHtml('splprice_start_date'); ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-2">
                    <div class="field-set">
                        <div class="field-wraper">
                            <?php echo $frm->getFieldHtml('splprice_end_date'); ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3">
                    <div class="field-set">
                        <div class="field-wraper">
                            <?php echo $frm->getFieldHtml('splprice_price'); ?>
                             <div class="js-discount-percentage"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-2">
                    <div class="field-set">
                        <div class="field-wraper">
                            <div class="field_cover">
                                <?php echo $frm->getFieldHtml('btn_update'); ?>
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
