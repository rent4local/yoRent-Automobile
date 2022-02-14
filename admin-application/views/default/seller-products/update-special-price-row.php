<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$editListingFrm = new Form('editListingFrm-' . $splPriceId, array('id' => 'editListingFrm-' . $splPriceId));
$productFor = $data['product_for'];
$selprodPrice = ($productFor == Product::PRODUCT_FOR_RENT) ? $data['sprodata_rental_price'] : $data['selprod_price'];
?>
<tr id='row-<?php echo $splPriceId; ?>'>
    <td>
        <label class="checkbox">
            <input class="selectItem--js" type="checkbox" name="selprod_ids[<?php echo $splPriceId; ?>]" value="<?php echo $data['splprice_selprod_id']; ?>"></label>
    </td>
    <td>
        <?php echo html_entity_decode($data['product_name']); ?>
    </td>
    <td>
        <?php echo CommonHelper::displayMoneyFormat($selprodPrice); ?>
    </td>
    <td>
        <?php echo $data['credential_username']; ?>
    </td>
    <td>
        <?php $startDate = date('Y-m-d', strtotime($data['splprice_start_date'])); ?>
        <div class="js--editCol edit-hover"><?php echo $startDate; ?></div>
        <?php
        $lbl = Labels::getLabel('LBL_Start_Date', $adminLangId);
        $attr = array(
            'readonly' => 'readonly',
            'placeholder' => $lbl,
            'data-selprodid' => $data['splprice_selprod_id'],
            'data-id' => $splPriceId,
            'data-oldval' => $startDate,
            'id' => 'splprice_start_date-' . $splPriceId,
            'class' => 'date_js js--splPriceCol hide sp-input',
        );
        $editListingFrm->addDateField($lbl, 'splprice_start_date', $startDate, $attr);
        echo $editListingFrm->getFieldHtml('splprice_start_date');
        ?>
    </td>
    <td>
        <?php $endDate = date('Y-m-d', strtotime($data['splprice_end_date'])); ?>
        <div class="js--editCol edit-hover"><?php echo $endDate; ?></div>
        <?php
        $lbl = Labels::getLabel('LBL_End_Date', $adminLangId);
        $attr = array(
            'readonly' => 'readonly',
            'placeholder' => $lbl,
            'data-selprodid' => $data['splprice_selprod_id'],
            'data-id' => $splPriceId,
            'data-oldval' => $endDate,
            'id' => 'splprice_end_date-' . $splPriceId,
            'class' => 'date_js js--splPriceCol hide sp-input',
        );
        $editListingFrm->addDateField($lbl, 'splprice_end_date', $endDate, $attr);
        echo $editListingFrm->getFieldHtml('splprice_end_date');
        ?>
    </td>
    <td>
        <div class="js--editCol edit-hover"><?php echo CommonHelper::displayMoneyFormat($data['splprice_price']); ?></div>
        <input type="text" data-price="<?php echo $selprodPrice; ?>" data-displayoldval="<?php echo CommonHelper::displayMoneyFormat($data['splprice_price'], true, true); ?>" data-id="<?php echo $splPriceId; ?>" value="<?php echo $data['splprice_price']; ?>" data-selprodid="<?php echo $data['splprice_selprod_id']; ?>" data-oldval="<?php echo $data['splprice_price']; ?>" name="splprice_price" class="js--splPriceCol hide sp-input" />
        <div class="ml-3 js--percentVal">
            <?php
            /* $discountPrice = $data['selprod_price'] - $data['splprice_price'];
            $discountPercentage = round(($discountPrice/$data['selprod_price'])*100, 2);
            $discountPercentage = $discountPercentage."% ".Labels::getLabel('LBL_off', $adminLangId);
            echo $discountPercentage;  */
            $discountText = '';
            if (($productFor == Product::PRODUCT_FOR_RENT && $data['sprodata_rental_price'] > $data['splprice_price']) || ($productFor == Product::PRODUCT_FOR_SALE && $data['selprod_price'] > $data['splprice_price'])) {
                $discountPrice = $selprodPrice - $data['splprice_price'];
                $discountPercentage = round(($discountPrice / $selprodPrice) * 100, 2);
                $discountText = $discountPercentage . "% " . Labels::getLabel('LBL_off', $adminLangId);
            }

            if (($productFor == Product::PRODUCT_FOR_RENT && $data['sprodata_rental_price'] < $data['splprice_price']) || ($productFor == Product::PRODUCT_FOR_SALE && $data['selprod_price'] < $data['splprice_price'])) {
                $discountValue = $selprodPrice - $data['splprice_price'];
                $discountValue = abs($discountValue);
                $discountText = Labels::getLabel('LBL_Extra_charges', $adminLangId) . ": " . CommonHelper::displayMoneyFormat($discountValue, true, true);
            }
            echo $discountText;
            ?>
        </div>

    </td>
    <td>
        <a href="javascript:void(0)" class="btn btn-clean btn-sm btn-icon" title="<?php echo Labels::getLabel('LBL_Delete', $adminLangId); ?>" onclick="deleteSellerProductSpecialPrice(<?php echo $splPriceId; ?>)"><i class='fa fa-trash  icon'></i></a>
    </td>
</tr>