<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$selProdId = (!empty($data['splprice_selprod_id']) ? $data['splprice_selprod_id'] : 0);
$frm = SellerProduct::specialPriceForm($adminLangId, $productFor);
$prodName = $frm->addSelectBox(Labels::getLabel('LBL_Product', $adminLangId), 'product_name', [], '', array('class' => 'selProd--js','placeholder' => Labels::getLabel('LBL_Select_Product', $adminLangId)));
//$prodName = $frm->addTextBox(Labels::getLabel('LBL_Product', $adminLangId), 'product_name', '', array('class' => 'selProd--js', 'placeholder' => Labels::getLabel('LBL_Select_Product', $adminLangId)));
$prodName->requirements()->setRequired();

$startDate = $frm->getField('splprice_start_date');
$startDate->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Price_Start_Date', $adminLangId));
$startDate->setFieldTagAttribute('class', 'date_js');
if (empty($data)) {
    $startDate->setFieldTagAttribute('disabled', 'disabled');
}

$endDate = $frm->getField('splprice_end_date');
$endDate->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Price_End_Date', $adminLangId));
$endDate->setFieldTagAttribute('class', 'date_js');
if (empty($data)) {
    $endDate->setFieldTagAttribute('disabled', 'disabled');
}

$splPrice = $frm->getField('splprice_price');
$splPrice->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Special_Price', $adminLangId));
if (empty($data)) {
    $splPrice->setFieldTagAttribute('disabled', 'disabled');
}

$splPrice->setFieldTagAttribute('class', 'js-special-price');

$frm->setFormTagAttribute('class', 'web_form');
$frm->setFormTagAttribute('onsubmit', 'updateSpecialPriceRow(this, ' . $selProdId . '); return(false);');
$frm->addHiddenField('', 'addMultiple');

$frm->setFormTagAttribute('id', 'frmAddSpecialPrice-' . $selProdId);
$frm->setFormTagAttribute('name', 'frmAddSpecialPrice-' . $selProdId);

$startDate = $frm->getField('splprice_start_date');
$startDate->setFieldTagAttribute('id', 'splprice_start_date' . $selProdId);

$endDate = $frm->getField('splprice_end_date');
$endDate->setFieldTagAttribute('id', 'splprice_end_date' . $selProdId);
$frm->addSubmitButton('', 'btn_update', Labels::getLabel('LBL_Save_Changes', $adminLangId));

if (!empty($data) && 0 < count($data)) {
    $prodName->setFieldTagAttribute('readonly', 'readonly');
    $frm->fill($data);
}

$selProdPrice = isset($data['selprod_price']) ? $data['selprod_price'] : 0;
$selProdPriceLbl = 0 < $selProdPrice ? Labels::getLabel("LBL_CURRENT_PRICE:_{PRICE}", $adminLangId) : '';
$selProdPriceLbl = !empty($selProdPriceLbl) ? CommonHelper::replaceStringData($selProdPriceLbl, ['{PRICE}' => $selProdPrice]) : '';
?>
<div class="card-body pt-4 pl-4 pr-4 pb-0">
    <div class="replaced">
        <?php
        echo $frm->getFormTag();
        echo $frm->getFieldHtml('product_for');
        echo $frm->getFieldHtml('splprice_selprod_id');
        echo $frm->getFieldHtml('addMultiple');
        echo $frm->getFieldHtml('product_for');

        ?>
        <div class="row">
            <div class="col-lg-3 col-md-3">
                <div class="field-set">
                    <div class="field-wraper">
                        <?php echo $frm->getFieldHtml('product_name'); ?>
                        <div class="js-prod-price" data-price="<?php echo $selProdPrice; ?>"><?php echo $selProdPriceLbl; ?></div>
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
            <div class="col-lg-2 col-md-2">
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
                            <?php
                            echo $frm->getFieldHtml('btn_update');
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </form>
        <?php echo $frm->getExternalJs(); ?>
    </div>
</div>
<div class="divider"></div>