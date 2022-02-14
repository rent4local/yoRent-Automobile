<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php if (!empty($records)) { ?>
<h6 class="pl-4 pt-4 pr-4"><?php echo Labels::getLabel('LBL_RFQ_Notifications', $siteLangId); ?></h6>
<ul class="list-notifications">
    <?php
    $statusArr = RequestForQuote::statusArray($siteLangId);
    foreach ($records as $notification) { ?>
    <li class=" <?php echo ($notification['unotification_is_read'] == 1) ? "note" : ""; ?>">
        <a href="javascript:void(0)" onclick="markNotificationRead(<?php echo $notification['unotification_id']; ?>)">
            <span class="grid">
                <span class="desc">
                    <?php
                                    $message = '';
                                    $notiData = json_decode($notification['unotification_data'], true);
                                    switch ($notification['unotification_type']) {
                                        case Notifications::NEW_RFQ_SUBMISSION :
                                            $selprodName = SellerProduct::getProductDisplayTitle($notiData['product_id'], $siteLangId);
                                            $message = sprintf(Labels::getLabel('LBL_NEW_QUOTATION_REQUEST_IS_SUBMITTED_FOR_YOUR_PRODUCT_%s', $siteLangId), $selprodName);
                                            if ($notiData['rfq_parent_id'] > 0) {
                                                $message = sprintf(Labels::getLabel('LBL_RE-QUOTATION_REQUEST_IS_SUBMITTED_FOR_YOUR_PRODUCT_%s', $siteLangId), $selprodName);
                                            }
                                            break;
                                        case Notifications::NEW_RFQ_OFFER_SUBMISSION_BY_SELLER :
                                        case Notifications::NEW_RFQ_OFFER_SUBMISSION_BY_BUYER :
                                            $message = sprintf(Labels::getLabel('LBL_OFFER_IS_SUBMITTED_BY_%s_ON_YOUR_Request', $siteLangId), $notiData['user_name']);
                                            break;
                                        case Notifications::NEW_RFQ_OFFER_UPDATE_BY_SELLER :
                                        case Notifications::NEW_RFQ_OFFER_UPDATE_BY_BUYER :
                                            $newStatus = $statusArr[$notiData['new_status_id']];
                                            $message = sprintf(Labels::getLabel('LBL_OFFER_STATUS_UPDATED_BY_%s_AS_%s', $siteLangId), $notiData['user_name'], $newStatus);
                                            break;
                                        case Notifications::INVOICE_SHARED_BY_SELLER :
                                            $message = Labels::getLabel('LBL_NEW_INVOICE_IS_GENERATED_FOR_YOUR_REQUEST', $siteLangId);
                                            break;
                                        case Notifications::RFQ_CLOSED_BY_ADMIN :
                                            $message = Labels::getLabel('LBL_RFQ_CLOSED_BY_ADMIN', $siteLangId);
                                            break;    
                                            
                                        case Notifications::INVOICE_REGENERATE_REQUEST_BY_BUYER :
                                            $message = sprintf(Labels::getLabel('LBL_NEW_INVOICE_REGENRATION_REQUEST_IS_SUBMITTED_BY_%s_FOR_ORDER_%s', $siteLangId), $notiData['user_name'], $notiData['order_id']);
                                            break;
                                    }

                                    echo $message;
                                    ?>
                </span>
                <span class="date"><?php echo FatDate::format($notification['unotification_date'], true); ?></span>
            </span>
        </a>
    </li>
    <?php } ?>
</ul>

<?php } ?>