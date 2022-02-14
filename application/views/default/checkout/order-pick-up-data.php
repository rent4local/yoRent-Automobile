<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
if (!empty($orderPickUpData)) {
    ?>
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span
                        class="primary-color"><?php echo Labels::getLabel('LBL_Pick_Up', $siteLangId); ?>
                    </span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul class="review-block">
                    <?php foreach ($orderPickUpData as $address) { ?>
                        <li>
                            <div class="review-block__label">
                                <strong><?php echo $address['op_selprod_title']; ?></strong>
                            </div>
                            <strong><?php echo ($address['opshipping_by_seller_user_id'] > 0) ? $address['op_shop_name'] : FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId, null, ''); ?></strong>
                            <div class="review-block__content">
                                <div class="delivery-address">
                                    <?php echo $address['oua_name']; ?>
                                    <p>
                                        <?php echo $address['oua_address1']; ?>
                                        <?php if (strlen($address['oua_address2']) > 0) {
                                            echo ", " . $address['oua_address2'];
                                            ?>
                                        <?php } ?>
                                    </p>
                                    <p><?php echo $address['oua_city'] . ", " . $address['oua_state']; ?></p>
                                    <p><?php echo $address['oua_country'] . ", " . $address['oua_zip']; ?></p>
                                    <?php if (strlen($address['oua_phone']) > 0) { ?>
                                        <p class="phone-txt"><i class="fas fa-mobile-alt"></i><?php echo $address['oua_phone']; ?></p>
                                    <?php } ?>
                                </div>
                            </div>
                            <!--div class="review-block__link">
                            <?php
                            //$fromTime = date('H:i', strtotime($address["opshipping_time_slot_from"]));
                            //$toTime = date('H:i', strtotime($address["opshipping_time_slot_to"]));
                            ?>
                                <p class="time-txt"><i
                                        class="fas fa-calendar-day"></i><?php //echo FatDate::format($address["opshipping_date"]) . ' ' . $fromTime . ' - ' . $toTime; ?>
                                </p>
                    
                            </div-->
                        </li>
                     <?php } ?>
                </ul>
                <div class="d-flex"><button class="btn btn-outline-brand btn-sm mleft-auto" type="button"
                                            onClick="ShippingSummaryData();"><?php echo Labels::getLabel('LBL_Edit', $siteLangId); ?></button></div>


            <?php } else { ?>
                <div class="pop-up-title"><?php echo Labels::getLabel('LBL_No_Pick_Up_address_added', $siteLangId); ?></div>
            <?php } ?>
        </div>
    </div>
</div>


<script>
    ShippingSummaryData = function () {
        /* $("#facebox .close").trigger('click'); */
        $("#exampleModal .close").click();
        loadShippingSummaryDiv();
    }
</script>