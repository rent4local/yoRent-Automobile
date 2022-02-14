<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
?>
<table cellspacing="0" cellpadding="0" border="0" width="100%" style="border:1px solid #ddd; border-collapse:collapse; text-align: left;">
    <thead>
        <tr style="text-align: center;">
            <th colspan="2" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;"><?php echo $rfqDetails['selprod_title']; ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;">
                <strong><?php echo Labels::getLabel('LBL_Request', $siteLangId); ?></strong> : #<?php echo $rfqDetails['rfq_id']; ?>
            </td>
            <td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;">
                <strong><?php echo Labels::getLabel('LBL_Date', $siteLangId); ?></strong> : 
                <?php
                if (isset($rfqDetails['counter_offer_id']) && $rfqDetails['counter_offer_id'] > 0) {
                    echo FatDate::format($rfqDetails['counter_offer_added_on']);
                } else {
                    echo FatDate::format($rfqDetails['rfq_added_on']);
                }
                ?>
            </td>
        </tr>
        <tr>
            <td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;">
                <strong><?php echo Labels::getLabel('LBL_User_Name', $siteLangId); ?></strong> : <?php echo $rfqDetails['sender_name']; ?>
            </td>
            <td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;">
                <strong><?php echo Labels::getLabel('LBL_Quality', $siteLangId); ?></strong> : <?php echo $rfqDetails['rfq_quantity']; ?>
            </td>
        </tr>
        <?php if ($rfqDetails['rfq_request_type'] == applicationConstants::PRODUCT_FOR_RENT) { ?>
        <tr>
            <td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;">
                <strong><?php echo Labels::getLabel('LBL_Requested_Rental_Start_Date', $siteLangId); ?></strong> :
                <?php
                if (isset($rfqDetails['counter_offer_id']) && $rfqDetails['counter_offer_id'] > 0) {
                    echo ($rfqDetails['counter_offer_from_date'] != '0000-00-00 00:00:00') ? FatDate::format($rfqDetails['counter_offer_from_date']) : Labels::getLabel('LBL_N/A', $siteLangId);
                } else {
                    echo ($rfqDetails['rfq_from_date'] != '0000-00-00 00:00:00') ? FatDate::format($rfqDetails['rfq_from_date']) : Labels::getLabel('LBL_N/A', $siteLangId);
                }
                ?>
                <br />
                <strong><?php echo Labels::getLabel('LBL_Requested_Rental_End_Date', $siteLangId); ?></strong> :
                <?php
                if (isset($rfqDetails['counter_offer_id']) && $rfqDetails['counter_offer_id'] > 0) {
                    echo ($rfqDetails['counter_offer_to_date'] != '0000-00-00 00:00:00') ? FatDate::format($rfqDetails['counter_offer_to_date']) : Labels::getLabel('LBL_N/A', $siteLangId);
                } else {
                    echo ($rfqDetails['rfq_to_date'] != '0000-00-00 00:00:00') ? FatDate::format($rfqDetails['rfq_to_date']) : Labels::getLabel('LBL_N/A', $siteLangId);
                }
                ?>
            </td>    
            <td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;">
                <strong><?php echo Labels::getLabel('LBL_Rental_Security_Amount', $siteLangId); ?></strong> :
                <?php echo CommonHelper::displayMoneyFormat($rfqDetails['counter_offer_rental_security']); ?>
            </td>
        </tr>
        <?php } ?>

        <?php if (isset($rfqDetails['counter_offer_id']) && $rfqDetails['counter_offer_id'] > 0) { 
            $colspan = 1;
            if ($rfqDetails['rfq_fulfilment_type'] == Shipping::FULFILMENT_PICKUP) {
                $colspan = 2;
            }
            ?>
            <tr>
                <td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" colspan="<?php echo $colspan;?>">
                    <strong><?php echo Labels::getLabel('LBL_Offer_Price', $siteLangId); ?></strong> : <?php echo CommonHelper::displayMoneyFormat($rfqDetails['counter_offer_total_cost']); ?>
                </td>
                <?php if ($rfqDetails['rfq_fulfilment_type'] == Shipping::FULFILMENT_SHIP) { ?>
                <td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;">
                    <strong><?php echo Labels::getLabel('LBL_Shipping_Cost', $siteLangId); ?></strong> : <?php echo CommonHelper::displayMoneyFormat($rfqDetails['counter_offer_shipping_cost']); ?>
                </td>
                <?php } ?>
            </tr>
        <?php } else { ?>
            <tr>
                <td colspan="2" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;">
                    <strong><?php echo Labels::getLabel('LBL_Comments', $siteLangId); ?></strong> : <?php echo $rfqDetails['rfq_comments']; ?>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>