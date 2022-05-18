<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php if (!empty($optionCombinations)) {  ?>
    <div class="variants-wrap js-scrollable table-wrap">
        <table width="100%" class="table-fixed-header">
            <thead>
                <tr>
                    <th width="20%"><?php echo Labels::getLabel('LBL_#', $siteLangId); ?></th>
                    <th width="40%"><?php echo Labels::getLabel('LBL_Variants', $siteLangId); ?></th>
                    <th width="40%"><?php echo Labels::getLabel('LBL_EAN/UPC', $siteLangId); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $count = 0;
                foreach ($optionCombinations as $optionValueId => $optionValue) {
                    $arr = explode('|', $optionValue);
                    $key = str_replace('|', ',', $optionValueId);
                    $variant = $optionValue;
                    $count++;
                ?>
                    <tr>
                        <td width="20%"><?php echo $count; ?></td>
                        <td width="40%"><?php echo $variant; ?></td>
                        <td width="40%">
                            <div class="field-set">
                                <div class="field-wraper">
                                    <div class="field_cover">
                                        <input type="text" id="code<?php echo $optionValueId; ?>" name="code<?php echo $optionValueId ?>" value="<?php echo (isset($upcCodeData[$key]['upc_code'])) ? $upcCodeData[$key]['upc_code'] : ''; ?>" onBlur="updateUpc('<?php echo $productId; ?>','<?php echo $optionValueId; ?>')" class="form-control">                   
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
<?php } else { ?>
    <div class="row mt-2">
        <div class="col-md-12">
            <div class="field-set">
                <div class="caption-wraper"><label class="field_label"><?php echo Labels::getLabel('LBL_EAN/UPC_code', $siteLangId); ?></label></div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <input type="text" id="code0" name="code0" value="<?php echo (isset($upcCodeData[0]['upc_code'])) ? $upcCodeData[0]['upc_code'] : ''; ?>" onBlur="updateUpc('<?php echo $productId; ?>','0')"  class="form-control">
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>