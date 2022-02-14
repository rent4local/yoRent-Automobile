<section class="section">
    <div class="container container--fluid">
        <ul class="highlights-wrapper">
            <?php
            if (!empty($product['product_warranty'])) { ?>
                <li>
                    <div class="highlights">
                        <div class="highlights__icon">
                            <i class="icn">
                                <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#yearswarranty" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#yearswarranty">
                                    </use>
                                </svg>
                            </i>
                        </div>
                        <div class="highlights__feature">
                            <?php
                            $lbl = Labels::getLabel('LBL_{DAYS}_DAYS_WARRANTY_FOR_SALE_ONLY', $siteLangId);
                            ?>
                            <h4><?php echo Labels::getLabel('LBL_Product_Warranty', $siteLangId); ?></h4>
                            <p><?php echo CommonHelper::replaceStringData($lbl, ['{DAYS}' => $product['product_warranty']]); ?></p>
                        </div>
                    </div>
                </li>
            <?php } ?>
            <?php if ((!empty($product['shop_return_age']) || !empty($product['selprod_return_age'])) && Product::PRODUCT_TYPE_PHYSICAL == $product['product_type']) { ?>
                <li>
                    <div class="highlights">
                        <div class="highlights__icon">
                            <i class="icn">
                                <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#easyreturns" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#easyreturns">
                                    </use>
                                </svg>
                            </i>
                        </div>
                        <div class="highlights__feature">
                            <h4><?php echo Labels::getLabel('LBL_Return_Policy', $siteLangId); ?></h4>
                            <p><?php
                                $lbl = Labels::getLabel('LBL_{DAYS}_DAYS_RETURN_BACK_POLICY_FOR_SALE_ONLY', $siteLangId);
                                $returnAge = !empty($product['selprod_return_age']) ? $product['selprod_return_age'] : $product['shop_return_age'];
                                $returnAge = !empty($returnAge) ? $returnAge : 0;
                                echo CommonHelper::replaceStringData($lbl, ['{DAYS}' => $returnAge]);
                                ?></p>
                        </div>
                    </div>
                </li>
            <?php } ?>
            <?php if ((!empty($product['shop_cancellation_age']) || !empty($product['selprod_cancellation_age']) || !empty($orderCancelPenaltyRules)) && Product::PRODUCT_TYPE_PHYSICAL == $product['product_type']) { ?>
                <li>
                    <div class="highlights">
                        <div class="highlights__icon">
                            <i class="icn">
                                <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#cancel-policy" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#cancel-policy">
                                    </use>
                                </svg>
                            </i>
                        </div>
                        <div class="highlights__feature">
                            <h4><?php echo Labels::getLabel('LBL_Shop_Cancellation', $siteLangId); ?></h4>
                            <a href="javascript:void(0);" data-toggle="modal" data-target="#cancellationModal">
                                <p style="text-decoration: underline;"><?php echo Labels::getLabel('LBL_View_details', $siteLangId); ?>
                                </p>
                            </a>
                        </div>
                    </div>
                </li>


                <div class="modal fade" id="cancellationModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" style="display:none;">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">
                                    <?php echo Labels::getLabel('LBL_Cancellation_Policy', $siteLangId); ?>
                                </h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <?php if (FatApp::getConfig('CONF_ALLOW_PENALTY_ON_RENTAL_ORDER_CANCEL_FROM_BUYER', FatUtility::VAR_INT, 0)) { ?>
                                    <!-- <div id="penaltyRules" style="display:none;"> -->
                                    <h6 style="font-weight: 700;"><?php echo Labels::getLabel('LBL_For_Rental_Orders', $siteLangId); ?></h6>
                                    <?php
                                    if (!empty($orderCancelPenaltyRules)) {
                                        echo '<table class="table mb-4">
                                    <tr>
                                    <th>' . Labels::getLabel('LBL_Cancellation_Duration(Hours)', $siteLangId) . '</th>
                                    <th>' . Labels::getLabel('LBL_Refundable_Amount', $siteLangId) . '</th>
                                    </tr>';
                                        foreach ($orderCancelPenaltyRules as $rule) { ?>
                                            <tr>
                                                <td>
                                                    <?php $maxDurationLabel = ($rule['ocrule_duration_max'] == -1) ? Labels::getLabel('LBL_Infinity', $siteLangId) : $rule['ocrule_duration_max'];  ?>


                                                    <?php echo $rule['ocrule_duration_min'] . ' - ' . $maxDurationLabel . ' ' . Labels::getLabel('LBL_Hours', $siteLangId); ?>

                                                </td>
                                                <td><?php echo $rule['ocrule_refund_amount'] . ' %'; ?></td>
                                                <?php /* echo sprintf(Labels::getLabel('LBL_if_Order_cancel_before_%s_hours_then_%s_amount_will_be_refunded', $siteLangId), $rule['ocrule_duration'], $rule['ocrule_refund_amount'] . '%'); */ ?>
                                            </tr>
                                    <?php
                                        }
                                        echo '</table>';
                                    }
                                    ?>
                                <?php } ?>
                                <?php
                                if (ALLOW_SALE && $availableForSale) {
                                    if (!empty($product['shop_cancellation_age']) || !empty($product['selprod_cancellation_age'])) { ?>
                                        <h6 style="font-weight: 700;"><?php echo Labels::getLabel('LBL_For_Sale_Orders', $siteLangId); ?> </h6>
                                        <p class="small mb-2"><?php
                                                                $lbl = Labels::getLabel('LBL_{DAYS}_DAYS_CANCELLATION_POLICY', $siteLangId);
                                                                $cancellationAge = !empty($product['selprod_cancellation_age']) ? $product['selprod_cancellation_age'] : $product['shop_cancellation_age'];
                                                                $cancellationAge = !empty($cancellationAge) ? $cancellationAge : 0;
                                                                echo CommonHelper::replaceStringData($lbl, ['{DAYS}' => $cancellationAge]); ?>
                                        </p>
                                <?php }
                                } ?>
                                <!-- </div> -->
                            </div>
                        </div>
                    </div>
                </div>

            <?php } ?>
            <?php if ($codEnabled && Product::PRODUCT_TYPE_PHYSICAL == $product['product_type']) { ?>
                <li>
                    <div class="highlights">
                        <div class="highlights__icon">
                            <i class="icn">
                                <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#safepayments" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#safepayments">
                                    </use>
                                </svg>
                            </i>
                        </div>
                        <div class="highlights__feature">
                            <h4><?php echo Labels::getLabel('LBL_Cash_on_delivery', $siteLangId); ?></h4>
                            <p><?php echo Labels::getLabel('LBL_Cash_on_delivery_is_available', $siteLangId); ?>
                                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" data-container="body" title="<?php echo Labels::getLabel('MSG_Cash_on_delivery_available._Choose_from_payment_options', $siteLangId); ?>
                    "></i>
                            </p>
                        </div>
                    </div>
                </li>
            <?php } ?>
            <?php if (Product::PRODUCT_TYPE_PHYSICAL == $product['product_type']) {
                $icon = $fulfillmentType == Shipping::FULFILMENT_PICKUP ? 'item_pickup' : 'freeshipping';
            ?>
                <li>
                    <div class="highlights">
                        <div class="highlights__icon">
                            <i class="icn">
                                <svg class="svg">
                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#<?php echo $icon; ?>" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#<?php echo $icon; ?>">
                                    </use>
                                </svg>
                            </i>
                        </div>
                        <div class="highlights__feature">
                            <h4><?php echo Labels::getLabel('LBL_Shipping_/_Pickup', $siteLangId); ?></h4>

                            <a href="javascript:void(0);" data-toggle="modal" data-target="#shippickmodal">
                                <p style="text-decoration: underline;"><?php echo Labels::getLabel('LBL_View_details', $siteLangId); ?>
                                </p>
                            </a>
                        </div>
                    </div>
                </li>
                <div class="modal fade" id="shippickmodal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" style="display:none;">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">
                                    <?php echo Labels::getLabel('LBL_Shipping_/_Pickup', $siteLangId); ?>
                                </h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <h6 style="font-weight: 700;"><?php echo Labels::getLabel('LBL_For_Rental_Orders', $siteLangId); ?></h6>
                                <table class="table mb-4">
                                    <tbody>
                                        <tr>
                                            <th><?php
                                                switch ($fulfillmentType) {
                                                    case Shipping::FULFILMENT_SHIP:
                                                        echo Labels::getLabel('LBL_SHIPPED_ONLY', $siteLangId);
                                                        break;
                                                    case Shipping::FULFILMENT_PICKUP:
                                                        echo Labels::getLabel('LBL_PICKUP_ONLY', $siteLangId);
                                                        break;
                                                    default:
                                                        echo Labels::getLabel('LBL_SHIPPMENT_AND_PICKUP', $siteLangId);
                                                        break;
                                                }
                                                ?></th>
                                        </tr>

                                    </tbody>
                                </table>
                                <?php if (ALLOW_SALE && $availableForSale) { ?>
                                    <h6 style="font-weight: 700;"><?php echo Labels::getLabel('LBL_For_Sale_Orders', $siteLangId); ?> </h6>
                                    <table class="table mb-4">
                                        <tbody>
                                            <tr>
                                                <th><?php
                                                    switch ($fulfillmentTypeSale) {
                                                        case Shipping::FULFILMENT_SHIP:
                                                            echo Labels::getLabel('LBL_SHIPPED_ONLY', $siteLangId);
                                                            break;
                                                        case Shipping::FULFILMENT_PICKUP:
                                                            echo Labels::getLabel('LBL_PICKUP_ONLY', $siteLangId);
                                                            break;
                                                        default:
                                                            echo Labels::getLabel('LBL_SHIPPMENT_AND_PICKUP', $siteLangId);
                                                            break;
                                                    }
                                                    ?></th>
                                            </tr>

                                        </tbody>
                                    </table>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </ul>
    </div>
</section>

<!-- Below code used to remove above bar if empty. -->
<script>
    $(document).ready(function() {
        if (1 > $('.shippingBar-js ul li').length) {
            $('.shippingBar-js').remove();
        }
    });
</script>