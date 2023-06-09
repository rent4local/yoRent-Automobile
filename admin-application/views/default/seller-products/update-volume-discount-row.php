<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<tr id="row-<?php echo $volDiscountId; ?>">
    <td>
        <label class="checkbox">
            <input class="selectItem--js" type="checkbox" name="selprod_ids[<?php echo $volDiscountId; ?>]" value="<?php echo $data['voldiscount_selprod_id']; ?>"></label>
    </td>
    <td>
        <?php echo html_entity_decode($data['product_name']); ?>
    </td>
    <td>
        <?php echo $data['credential_username']; ?>
    </td>
    <td>
        <div class="js--editCol edit-hover"><?php echo $data['voldiscount_min_qty']; ?></div>
        <input type="text" data-id="<?php echo $volDiscountId; ?>" value="<?php echo $data['voldiscount_min_qty']; ?>" data-selprodid="<?php echo $data['voldiscount_selprod_id']; ?>" name="voldiscount_min_qty" class="js--volDiscountCol hide vd-input" data-oldval="<?php echo $data['voldiscount_min_qty']; ?>"/>
    </td>
    <td>
        <div class="js--editCol edit-hover"><?php echo number_format((float)$data['voldiscount_percentage'], 2, '.', ''); ?></div>
        <input type="text" data-id="<?php echo $volDiscountId; ?>" value="<?php echo $data['voldiscount_percentage']; ?>" data-selprodid="<?php echo $data['voldiscount_selprod_id']; ?>" name="voldiscount_percentage" class="js--volDiscountCol hide vd-input" data-oldval="<?php echo $data['voldiscount_percentage']; ?>"/>
    </td>
    <td>
		<a href="javascript:void(0)" class="btn btn-clean btn-sm btn-icon" title="<?php echo Labels::getLabel('LBL_Delete', $adminLangId); ?>" onclick="deleteSellerProductVolumeDiscount(<?php echo $volDiscountId; ?>)"><i class="fa fa-trash  icon"></i></a>
    </td>
</tr>
