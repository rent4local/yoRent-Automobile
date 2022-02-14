<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if (!empty($combTaxes) && count($combTaxes) > 0) { ?>
    <table class="table table-bordered table-hover table-edited mt-4">
        <thead>
            <tr>
                <th width="60%"><?php echo Labels::getLabel('LBL_Name', $adminLangId) ?></th>
                <th width="30%"><?php echo Labels::getLabel('LBL_Tax_Rate', $adminLangId) ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($combTaxes as $key => $val) { ?>
                <tr class="rule-detail-row--js rule-detail-row0">
                    <td scope="row">                 
                        <input type="hidden" name="combinedTaxDetails[<?php echo $key; ?>][taxruledet_taxstr_id]" value="<?php echo $val['taxstr_id']; ?>">
                        <input title="<?php echo Labels::getLabel('LBL_Name', $adminLangId) ?>" type="text" name="combinedTaxDetails[<?php echo $key; ?>][taxstr_name]" value="<?php echo $val['taxstr_name']; ?>" readonly>
                    </td>
                    <td>
                        <input title="<?php echo Labels::getLabel('LBL_Tax_Rate(%)', $adminLangId) ?>" type="text" name="combinedTaxDetails[<?php echo $key; ?>][taxruledet_rate]" class='combinetaxvalue--js' value="<?php echo ($val['taxruledet_rate']) ? $val['taxruledet_rate'] : 0; ?>">
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
<?php } ?>