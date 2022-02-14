<?php
defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="modal-dialog modal-dialog-centered" role="document" id="pick-up-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><?php echo Labels::getLabel('LBL_Pick_Up', $siteLangId); ?></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <?php
            if (!empty($addresses)) {
            ?>
            <div class="pick-section">
                <div class="pickup-option">
                    <ul class="pickup-option__list">
                        <?php foreach ($addresses as $key => $address) { ?>
                        <li>
                            <label class="radio">
                                <input name="pickup_address" type="radio" value="<?php echo $address['addr_id']; ?>"
                                    <?php if($selectedAddr == $address['addr_id'] || $key == 0) { echo "checked='checked'"; }?>>

                                <span class="lb-txt js-addr-<?php echo $address['addr_id']; ?>">
                                    <p><?php echo $address['addr_name'] . ', ' . $address['addr_address1']; ?>
                                        <?php
                                                if (strlen($address['addr_address2']) > 0) {
                                                    echo ", " . $address['addr_address2'];
                                                ?>
                                        <?php } ?>
                                    </p>
                                    <p><?php echo $address['addr_city'] . ", " . $address['state_name']; ?></p>
                                    <p><?php echo $address['country_name'] . ", " . $address['addr_zip']; ?></p>
                                    <?php if (strlen($address['addr_phone']) > 0) { ?>
                                    <span class="phone-txt"><i
                                            class="fas fa-mobile-alt"></i><?php echo $address['addr_phone']; ?></span>
                                    <?php } ?>
                                </span>
                            </label>
                        </li>
                        <?php } ?>
                    </ul>
                </div>

            </div>
            <?php } else { ?>
            <h5><?php echo Labels::getLabel('LBL_No_Pick_Up_address_added', $siteLangId); ?></h5>
            <?php } ?>
        </div>
        <div class="modal-footer"> <a class="btn btn-brand btn-wide "
                onclick="setPickupAddress('<?php echo $srNo; ?>');"
                href="javascript:void(0)"><?php echo Labels::getLabel('LBL_Continue', $siteLangId); ?></a></div>
    </div>
</div>