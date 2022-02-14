<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="addonModalLabel"><?php echo Labels::getLabel('LBL_Available_Pickup_Locations', $siteLangId); ?></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <?php if (!empty($addresses)) { ?>
            <ul class="my-addresses">
            <?php foreach ($addresses as $address) { ?>
                <li>
                    <div class="my-addresses__body">
                        <address class="delivery-address">
                            <h5><?php echo $address['addr_name']; ?><span class="tag"><?php echo $address['addr_title']; ?></span></h5>
                            <p>
                                <?php echo $address['addr_address1'] . '<br>'; ?>
                                <?php echo (strlen($address['addr_address2']) > 0) ? $address['addr_address2'] . '<br>' : ''; ?>
                                <?php echo (strlen($address['addr_city']) > 0) ? $address['addr_city'] . ',' : ''; ?>
                                <?php echo (strlen($address['state_name']) > 0) ? $address['state_name'] . '<br>' : ''; ?>
                                <?php echo (strlen($address['country_name']) > 0) ? $address['country_name'] . '<br>' : ''; ?>
                                <?php echo (strlen($address['addr_zip']) > 0) ? Labels::getLabel('LBL_Zip:', $siteLangId) . $address['addr_zip'] . '<br>' : ''; ?>
                            </p>
                            <p class="phone-txt">
                                <i class="fas fa-mobile-alt"></i>
                                <?php echo (strlen($address['addr_phone']) > 0) ? Labels::getLabel('LBL_Phone:', $siteLangId) . $address['addr_phone'] . '<br>' : ''; ?>
                            </p>
                        </address>
                    </div>
                </li>
            <?php } ?>
            </ul>
            <?php } else { ?>
                <p class="text-center"><?php echo Labels::getLabel('LBL_Pickup_Address_Not_Added', $siteLangId); ?></p>
            <?php } ?>
        </div>
    </div>
</div>