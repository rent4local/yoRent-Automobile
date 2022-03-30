 <?php defined('SYSTEM_INIT') or die('Invalid Usage . ');
    $addonTotal = 0;
    $isShipping = false;
    $cartTotalAmount = CommonHelper::orderProductAmount($childOrder, 'cart_total');
    $couponDiscount = CommonHelper::orderProductAmount($childOrder, 'DISCOUNT');
    $shippingCharges = CommonHelper::orderProductAmount($childOrder, 'SHIPPING');
    $totalTaxes = CommonHelper::orderProductAmount($childOrder, 'TAX');
    $volumnDiscountAmount = CommonHelper::orderProductAmount($childOrder, 'VOLUME_DISCOUNT');
    $durationDiscountAmount = CommonHelper::orderProductAmount($childOrder, 'DURATION_DISCOUNT');
    $rewardPointTotal = CommonHelper::orderProductAmount($childOrder, 'REWARDPOINT');
    $totalSecurityAmount = $childOrder['opd_rental_security'] * $childOrder['op_qty'];
    $orderNetTotal = CommonHelper::orderProductAmount($childOrder, 'NETAMOUNT');
    $roundingOffTotal = $childOrder['op_rounding_off'];
    $services = $childOrder['services'];

    $isRentOrder = 0;
    $itemWidth = "45%";
    $qtyWidth = "15%";
    $priceWidth = "20%";
    $totalWidth = "20%";
    if ($childOrder['opd_sold_or_rented'] == applicationConstants::PRODUCT_FOR_RENT) {
        $isRentOrder = 1;
        $qtyWidth = "10%";
        $priceWidth = "15%";
        $totalWidth = "15%";
    }
    ?>
 <table width="100%">
     <tbody>
         <tr>
             <table width="100%">
                 <tbody>
                     <tr>
                         <td valign="middle"><img src="<?php echo $logoImgUrl; ?>"></td>
                         <td align="right" valign="middle"><?php echo Labels::getlabel('LBL_Order_details', $siteLangId); ?></td>
                     </tr>
                 </tbody>
             </table>
         </tr>
     
         <tr>
             <table width="100%" border="1">
                 <tbody>
                     <tr>&nbsp;</tr>
                 </tbody>
             </table>
         </tr>
         <tr>
             <td>&nbsp;</td>
         </tr>
         <tr>
             <table width="100%">
                 <tbody>
                     <tr>
                         <td><strong><?php echo Labels::getLabel('LBL_Date', $siteLangId); ?>:</strong>
                             <font size="10"><?php echo FatDate::format($childOrder['order_date_added']); ?></font>
                         </td>
                         <td align="right"><strong><?php echo Labels::getLabel('LBL_Order_Id', $siteLangId); ?>:</strong>
                             <font size="10"><?php echo $childOrder['op_invoice_number']; ?></font>
                         </td>
                     </tr>
                 </tbody>
             </table>
         </tr>
         <tr>
             <td>&nbsp;</td>
         </tr>
         <tr>
             <table width="100%" border="1">
                 <tbody>
                     <tr>&nbsp;</tr>
                 </tbody>
             </table>
         </tr>
         <tr>
             <td>&nbsp;</td>
         </tr>
         <tr>
             <table width="100%">
                 <tbody>
                     <tr>
                         <?php
                            $billingAddress = '<br><font size="10">' . $orderDetail['billingAddress']['oua_name'] . '</font><br/>';
                            if ($orderDetail['billingAddress']['oua_address1'] != '') {
                                $billingAddress .= '<font size="10">' . $orderDetail['billingAddress']['oua_address1'] . '</font><br/>';
                            }

                            if ($orderDetail['billingAddress']['oua_address2'] != '') {
                                $billingAddress .= '<font size="10">' . $orderDetail['billingAddress']['oua_address2'] . '</font><br/>';
                            }

                            if ($orderDetail['billingAddress']['oua_city'] != '') {
                                $billingAddress .= '<font size="10">' . $orderDetail['billingAddress']['oua_city'] . ', ';
                            }

                            if ($orderDetail['billingAddress']['oua_state'] != '') {
                                $billingAddress .= $orderDetail['billingAddress']['oua_state'] . ', ';
                            }

                            if ($orderDetail['billingAddress']['oua_country'] != '') {
                                $billingAddress .= $orderDetail['billingAddress']['oua_country'];
                            }

                            if ($orderDetail['billingAddress']['oua_zip'] != '') {
                                $billingAddress .= '-' . $orderDetail['billingAddress']['oua_zip'];
                            }

                            if ($orderDetail['billingAddress']['oua_phone'] != '') {
                                $billingAddress .= '</font><br/><font size="10">' . $orderDetail['billingAddress']['oua_dial_code'] . ' ' . $orderDetail['billingAddress']['oua_phone'] . '</font>';
                            }
                            ?>
                         <td width="45%"><strong><?php echo Labels::getLabel('LBL_Bill_To', $siteLangId); ?>:</strong>
                             <br>
                             <?php echo $billingAddress; ?>
                         </td>
                         <td width="10%">&nbsp;</td>
                         <?php if (!empty($orderDetail['shippingAddress'])) {
                                $isShipping = true;
                                $shippingAddress = '<br><font size="10">' . $orderDetail['shippingAddress']['oua_name'] . '</font><br/>';
                                if ($orderDetail['billingAddress']['oua_address1'] != '') {
                                    $shippingAddress .= '<font size="10">' . $orderDetail['shippingAddress']['oua_address1'] . '</font><br/>';
                                }

                                if ($orderDetail['shippingAddress']['oua_address2'] != '') {
                                    $shippingAddress .= '<font size="10">' . $orderDetail['shippingAddress']['oua_address2'] . '</font><br/>';
                                }

                                if ($orderDetail['shippingAddress']['oua_city'] != '') {
                                    $shippingAddress .= '<font size="10">' . $orderDetail['shippingAddress']['oua_city'] . ', ';
                                }

                                if ($orderDetail['shippingAddress']['oua_state'] != '') {
                                    $shippingAddress .= $orderDetail['shippingAddress']['oua_state'] . ', ';
                                }

                                if ($orderDetail['shippingAddress']['oua_country'] != '') {
                                    $shippingAddress .= $orderDetail['shippingAddress']['oua_country'];
                                }

                                if ($orderDetail['shippingAddress']['oua_zip'] != '') {
                                    $shippingAddress .= '-' . $orderDetail['shippingAddress']['oua_zip'];
                                }

                                if ($orderDetail['shippingAddress']['oua_phone'] != '') {
                                    $shippingAddress .= '</font><br/><font size="10">' . $orderDetail['shippingAddress']['oua_dial_code'] . ' ' . $orderDetail['shippingAddress']['oua_phone'] . '</font>';
                                }

                            ?>
                             <td width="45%" align="right"><strong><?php echo Labels::getLabel('LBL_Ship_To', $siteLangId); ?>:</strong>
                                 <br>
                                 <?php echo $shippingAddress; ?>
                             </td>
                         <?php } ?>

                         <?php if (!empty($orderDetail['pickupAddress'])) {
                                $pickUpAddress = '<br><font size="10">' . $orderDetail['pickupAddress']['oua_name'] . '</font><br/>';
                                if ($orderDetail['pickupAddress']['oua_address1'] != '') {
                                    $pickUpAddress .= '<font size="10">' . $orderDetail['pickupAddress']['oua_address1'] . '</font><br/>';
                                }

                                if ($orderDetail['pickupAddress']['oua_address2'] != '') {
                                    $pickUpAddress .= '<font size="10">' . $orderDetail['pickupAddress']['oua_address2'] . '</font><br/>';
                                }

                                if ($orderDetail['pickupAddress']['oua_city'] != '') {
                                    $pickUpAddress .= '<font size="10">' . $orderDetail['pickupAddress']['oua_city'] . ', ';
                                }

                                if ($orderDetail['pickupAddress']['oua_state'] != '') {
                                    $pickUpAddress .= $orderDetail['pickupAddress']['oua_state'] . ', ';
                                }

                                if ($orderDetail['pickupAddress']['oua_country'] != '') {
                                    $pickUpAddress .= $orderDetail['pickupAddress']['oua_country'];
                                }

                                if ($orderDetail['pickupAddress']['oua_zip'] != '') {
                                    $pickUpAddress .= '-' . $orderDetail['pickupAddress']['oua_zip'];
                                }

                                if ($orderDetail['pickupAddress']['oua_phone'] != '') {
                                    $pickUpAddress .= '</font><br/><font size="10">' . $orderDetail['pickupAddress']['oua_dial_code'] . ' ' . $orderDetail['pickupAddress']['oua_phone'] . '</font>';
                                }

                            ?>
                             <td width="45%" align="right"><strong><?php echo Labels::getLabel('LBL_Pick_Up', $siteLangId); ?>:</strong>
                                 <br>
                                 <?php echo $pickUpAddress; ?>
                             </td>
                         <?php } ?>
                     </tr>
                 </tbody>
             </table>
         </tr>
         <tr>
             <td>&nbsp;</td>
         </tr>
         <tr>
             <table width="100%" border="1">
                 <tbody>
                     <tr>&nbsp;</tr>
                 </tbody>
             </table>
         </tr>
         <tr>
             <td>&nbsp;</td>
         </tr>
         <tr>
             <table width="100%">
                 <tbody>
                     <tr>
                         <td><strong>
                                 <font size="10"><?php echo Labels::getLabel('LBL_Orders', $siteLangId); ?>:</font>
                             </strong>
                             <br><span>
                                 <font size="10"><?php echo $childOrder['op_order_id']; ?></font>
                             </span>
                         </td>

                         <?php if ($childOrder['opd_sold_or_rented'] == applicationConstants::PRODUCT_FOR_RENT) { ?>
                             <?php $duration = CommonHelper::getDifferenceBetweenDates($childOrder['opd_rental_start_date'], $childOrder['opd_rental_end_date'], $childOrder['op_selprod_user_id'], $childOrder['opd_rental_type']);
                                ?>
                             <td><strong>
                                     <font size="10"><?php echo Labels::getLabel('LBL_From', $siteLangId); ?>:</font>
                                 </strong>
                                 <br><span>
                                     <font size="10"><?php echo date('M d, Y ', strtotime($childOrder['opd_rental_start_date'])); ?></font>
                                 </span>
                             </td>
                             <td><strong>
                                     <font size="10"><?php echo Labels::getLabel('LBL_To', $siteLangId); ?>:</font>
                                 </strong>
                                 <br><span>
                                     <font size="10"><?php echo date('M d, Y ', strtotime($childOrder['opd_rental_end_date'])); ?></font>
                                 </span>
                             </td>
                         <?php }

                            $paymentMethodName = empty($childOrder['plugin_name']) ? $childOrder['plugin_identifier'] : $childOrder['plugin_name'];
                            if (!empty($paymentMethodName) && $childOrder['order_pmethod_id'] > 0 && $childOrder['order_is_wallet_selected'] > 0) {
                                $paymentMethodName .= ' + ';
                            }
                            if ($childOrder['order_is_wallet_selected'] > 0) {
                                $paymentMethodName .= Labels::getLabel("LBL_Wallet", $siteLangId);
                            }

                            ?>
                         <td><strong>
                                 <font size="10"><?php echo Labels::getLabel('LBL_Payment_Method', $siteLangId); ?>:</font>
                             </strong>
                             <br><span>
                                 <font size="10"><?php echo $paymentMethodName; ?></font>
                             </span>
                         </td>
                         <?php /* if ($childOrder['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT) { ?>
                             <td align="right"><strong>
                                     <font size="10"><?php echo Labels::getLabel('LBL_Duration', $siteLangId); ?>:</font>
                                 </strong>
                                 <br><span>
                                     <font size="10"><?php echo $duration . ' ' . Labels::getLabel('LBL_Days', $siteLangId); ?></font>
                                 </span>
                             </td>
                         <?php } */ ?>

                     </tr>
                 </tbody>
             </table>
         </tr>
         <tr>
             <td>&nbsp;</td>
         </tr>
         <tr>
             <table width="100%" border="1" cellpadding="5">
                 <thead>
                     <tr>
                         <th width="45%"><strong>
                                 <font size="10"><?php echo Labels::getLabel('LBL_Item', $siteLangId); ?></font>
                             </strong></th>
                         <th width="<?php echo $qtyWidth; ?>"><strong>
                                 <font size="10"><?php echo Labels::getLabel('LBL_Qty', $siteLangId); ?></font>
                             </strong></th>
                         <th width="<?php echo $priceWidth; ?>"><strong>
                                 <font size="10"><?php echo ($childOrder['opd_sold_or_rented'] == applicationConstants::PRODUCT_FOR_RENT) ? Labels::getLabel('LBL_Unit_Price', $siteLangId) : Labels::getLabel('LBL_Unit_Price', $siteLangId); ?></font>
                             </strong></th>
                         <?php if ($isRentOrder) { ?>
                             <th width="15%"><strong>
                                     <font size="10"><?php echo Labels::getLabel('LBL_Duration', $siteLangId); ?></font>
                                 </strong></th>
                         <?php } ?>
                         <th width="<?php echo $totalWidth; ?>" align="right"><strong>
                                 <font size="10"><?php echo Labels::getLabel('LBL_Total_Price', $siteLangId); ?></font>
                                 <?php if ($isRentOrder) { 
                                echo '<font size="8">('. Labels::getLabel('LBL_Total_Amount_tooltip', $siteLangId). ')</font>'; 
                            } ?>
                             </strong>
                         </th>
                     </tr>
                 </thead>
                 <tbody>
                     <tr>
                         <td width="45%">
                             <font size="10"><strong><?php
                                                        echo ($childOrder['op_selprod_title'] != '') ? $childOrder['op_selprod_title'] : $childOrder['op_product_name']; ?></strong></font>
                             <br>
                             <font size="10"><strong><?php echo Labels::getLabel('LBL_Brand', $siteLangId); ?>:</strong> <?php echo CommonHelper::displayNotApplicable($siteLangId, $childOrder['op_brand_name']); ?></font>
                             <?php if ($childOrder['op_selprod_options'] != '') { ?>
                                 <br>
                                 <font size="10"><strong><?php echo Labels::getLabel('LBL_Options', $siteLangId); ?>:</strong> <?php echo $childOrder['op_selprod_options']; ?></font>
                                 <br>
                             <?php } ?>


                             <?php if ($childOrder['op_shipping_duration_name'] != '') { ?>
                                 <br>
                                 <font size="10"><strong><?php echo Labels::getLabel('LBL_Shipping_Method', $siteLangId); ?>:</strong> <?php echo $childOrder['op_shipping_durations'] . '-' . $childOrder['op_shipping_duration_name']; ?></font>
                             <?php } ?>
                             <br>
                             <font size="10"><strong><?php echo Labels::getLabel('LBL_Sold_by', $siteLangId); ?>:</strong> <?php echo $childOrder['op_shop_name']; ?></font>
                             <br>

                             <?php if (!empty($services)) { ?>
                                 <br>
                                 <font size="10"><strong><?php echo Labels::getLabel('LBL_Addons', $siteLangId); ?>:</strong></font>
                                 <?php foreach ($services as $service) {
                                        $addonTotal += CommonHelper::orderProductAmount($service, 'cart_total');
                                        $couponDiscount += CommonHelper::orderProductAmount($service, 'DISCOUNT');
                                        $shippingCharges += CommonHelper::orderProductAmount($service, 'SHIPPING');
                                        $totalTaxes += CommonHelper::orderProductAmount($service, 'TAX');
                                        $volumnDiscountAmount += CommonHelper::orderProductAmount($service, 'VOLUME_DISCOUNT');
                                        $durationDiscountAmount += CommonHelper::orderProductAmount($service, 'DURATION_DISCOUNT');
                                        $rewardPointTotal += CommonHelper::orderProductAmount($service, 'REWARDPOINT');
                                        $totalSecurityAmount += $service['opd_rental_security'] * $service['op_qty'];
                                        $orderNetTotal += CommonHelper::orderProductAmount($service, 'NETAMOUNT');
                                        $roundingOffTotal += $service['op_rounding_off'];
                                    ?>
                                     <br>
                                     <font size="10"><?php
                                                        echo ($service['op_selprod_title'] != '') ? $service['op_selprod_title'] : $service['op_product_name']; ?></font>
                                 <?php } ?>


                             <?php } ?>

                         </td>
                         <td width="<?php echo $qtyWidth; ?>">
                             <font size="10"><?php echo $childOrder['op_qty']; ?></font>
                         </td>
                         <td width="<?php echo $priceWidth; ?>">
                            <font size="10"> 
                            <?php if ($isRentOrder) {
                                echo CommonHelper::displayMoneyFormat($childOrder['opd_duration_price'], true, false, true, false, true);  
                            } else {
                                echo CommonHelper::displayMoneyFormat($childOrder['op_unit_price'], true, false, true, false, true);
                            } ?>  
                            </font>
                         </td>
                         <?php if ($isRentOrder) { ?>
                             <td width="15%">
                                 <font size="10"><?php echo $duration . ' ' . Labels::getLabel('LBL_Days', $siteLangId); ?></font>
                             </td>
                         <?php } ?>
                         <td width="<?php echo $totalWidth; ?>" align="right">
                             <font size="10"><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($childOrder, 'CART_TOTAL'), true, false, true, false, true); ?></font>
                             
                         </td>
                     </tr>
                 </tbody>
             </table>
         </tr>
         <tr>
             <td>&nbsp;</td>
         </tr>
         <?php
            $couponDiscount = CommonHelper::orderProductAmount($childOrder, 'DISCOUNT');
            $volumeDiscount = CommonHelper::orderProductAmount($childOrder, 'VOLUME_DISCOUNT');
            $durationDiscount = CommonHelper::orderProductAmount($childOrder, 'DURATION_DISCOUNT');
            $totalSavings = $couponDiscount + $volumeDiscount + $durationDiscount;
            ?>
         <tr>
             <table width="100%" align="right">
                 <tbody>

                     <tr>
                         <td width="75%">
                             <font size="10"><strong><?php echo Labels::getLabel('LBL_Cart_total', $siteLangId); ?></strong></font>
                         </td>
                         <td width="5%">
                             <font size="10"><strong>:</strong></font>
                         </td>
                         <td width="20%">
                             <font size="10"> <?php echo CommonHelper::displayMoneyFormat($cartTotalAmount, true, false, true, false, true); ?></font>
                         </td>
                     </tr>
                     <?php if ($addonTotal > 0) { ?>
                         <tr>
                             <td width="75%">
                                 <font size="10"><strong><?php echo Labels::getLabel('LBL_Addons_Charges', $siteLangId) ?></strong></font>
                             </td>
                             <td width="5%">
                                 <font size="10"><strong>:</strong></font>
                             </td>
                             <td width="20%">
                                 <font size="10"> <?php echo CommonHelper::displayMoneyFormat($addonTotal, true, false, true, false, true); ?></font>
                             </td>
                         </tr>
                     <?php } ?>

                     <?php if ($durationDiscountAmount != 0) { ?>
                         <tr>
                             <td width="75%">
                                 <font size="10"><strong><?php echo Labels::getLabel('LBL_Duration_Discount', $siteLangId); ?></strong></font>
                             </td>
                             <td width="5%">
                                 <font size="10"><strong>:</strong></font>
                             </td>
                             <td width="20%">
                                 <font size="10"> <?php echo CommonHelper::displayMoneyFormat($durationDiscountAmount, true, false, true, false, true); ?></font>
                             </td>
                         </tr>
                     <?php } ?>

                     <?php if ($volumnDiscountAmount != 0) { ?>
                         <tr>
                             <td width="75%">
                                 <font size="10"><strong><?php echo Labels::getLabel('LBL_Volume_Discount', $siteLangId); ?></strong></font>
                             </td>
                             <td width="5%">
                                 <font size="10"><strong>:</strong></font>
                             </td>
                             <td width="20%">
                                 <font size="10"> <?php echo CommonHelper::displayMoneyFormat($volumnDiscountAmount, true, false, true, false, true); ?></font>
                             </td>
                         </tr>
                     <?php } ?>
                     <?php if ($totalSecurityAmount > 0) { ?>
                         <tr>
                             <td width="75%">
                                 <font size="10"><strong><?php echo Labels::getLabel('LBL_Security_Amount', $siteLangId); ?></strong></font>
                             </td>
                             <td width="5%">
                                 <font size="10"><strong>:</strong></font>
                             </td>
                             <td width="20%">
                                 <font size="10"> <?php echo CommonHelper::displayMoneyFormat($totalSecurityAmount, true, false, true, false, true); ?></font>
                             </td>
                         </tr>
                     <?php } ?>
                     <?php if ($isShipping > 0) { ?>
                         <tr>
                             <td width="75%">
                                 <font size="10"><strong><?php echo Labels::getLabel('LBL_Shipping_Price', $siteLangId); ?></strong></font>
                             </td>
                             <td width="5%">
                                 <font size="10"><strong>:</strong></font>
                             </td>
                             <td width="20%">
                                 <font size="10"> <?php echo CommonHelper::displayMoneyFormat($shippingCharges, true, false, true, false, true); ?></font>
                             </td>
                         </tr>
                     <?php } ?>
                     <tr>
                         <td width="75%">
                             <font size="10"><strong><?php echo Labels::getLabel('LBL_Taxes', $siteLangId); ?></strong></font>
                         </td>
                         <td width="5%">
                             <font size="10"><strong>:</strong></font>
                         </td>
                         <td width="20%">
                             <font size="10"> <?php echo CommonHelper::displayMoneyFormat($totalTaxes, true, false, true, false, true); ?></font>
                         </td>
                     </tr>
                     <?php if ($roundingOffTotal > 0) { ?>
                         <tr>
                             <td width="75%">
                                 <font size="10"><strong><?php echo Labels::getLabel('LBL_Rounding_Up', $siteLangId); ?></strong></font>
                             </td>
                             <td width="5%">
                                 <font size="10"><strong>:</strong></font>
                             </td>
                             <td width="20%">
                                 <font size="10"> <?php echo CommonHelper::displayMoneyFormat($roundingOffTotal, true, false, true, false, true); ?></font>
                             </td>
                         </tr>
                     <?php } elseif (0 > $roundingOffTotal) { ?>
                         <tr>
                             <td width="75%">
                                 <font size="10"><strong><?php echo Labels::getLabel('LBL_Rounding_Down', $siteLangId); ?></strong></font>
                             </td>
                             <td width="5%">
                                 <font size="10"><strong>:</strong></font>
                             </td>
                             <td width="20%">
                                 <font size="10"> <?php echo CommonHelper::displayMoneyFormat($roundingOffTotal, true, false, true, false, true); ?></font>
                             </td>
                         </tr>
                     <?php } ?>

                     <?php if ($couponDiscount != 0) { ?>
                         <tr>
                             <td width="75%">
                                 <font size="10"><strong><?php echo Labels::getLabel('LBL_Discount', $siteLangId); ?></strong></font>
                             </td>
                             <td width="5%">
                                 <font size="10"><strong>:</strong></font>
                             </td>
                             <td width="20%">
                                 <font size="10"> <?php echo CommonHelper::displayMoneyFormat($couponDiscount, true, false, true, false, true); ?></font>
                             </td>
                         </tr>
                     <?php } ?>

                     <?php if ($rewardPointTotal != 0) { ?>
                         <tr>
                             <td width="75%">
                                 <font size="10"><strong><?php echo Labels::getLabel('LBL_Reward_Point_Discount', $siteLangId); ?></strong></font>
                             </td>
                             <td width="5%">
                                 <font size="10"><strong>:</strong></font>
                             </td>
                             <td width="20%">
                                 <font size="10"> <?php echo CommonHelper::displayMoneyFormat($rewardPointTotal, true, false, true, false, true); ?></font>
                             </td>
                         </tr>

                     <?php } ?>
                     <tr>
                        <td width="75%">
                            <font size="12"><strong><?php echo Labels::getLabel('LBL_Order_Total_Amount', $siteLangId); ?></strong></font>
                        </td>
                        <td width="5%">
                            <font size="10"><strong>:</strong></font>
                        </td>
                        <td width="20%">
                            <font size="12"><strong><?php echo CommonHelper::displayMoneyFormat($orderNetTotal, true, false, true, false, true); ?></strong></font>
                        </td>
                    </tr>

                 </tbody>
             </table>
         </tr>
         <tr>
             <td>&nbsp;</td>
         </tr>
         <tr>
             <td>&nbsp;</td>
         </tr>
         <tr>
             <table>
                 <tbody>
                     <tr>
                         <td>
                             <font size="10"><strong><?php echo Labels::getLabel('LBL_Authorized_Signatory', $siteLangId); ?></strong></font>
                         </td>
                     </tr>
                 </tbody>
             </table>
         </tr>
         <tr>
             <td>&nbsp;</td>
         </tr>
         <tr>
             <table width="100%" border="1">
                 <tbody>
                     <tr>&nbsp;</tr>
                 </tbody>
             </table>
         </tr>
         <tr>
             <td>&nbsp;</td>
         </tr>
         <?php
            if (FatApp::getConfig("CONF_ADDRESS_ON_ORDER_DETAIL_PRINT") == User::USER_TYPE_SELLER) {
                $officeAddress = '<font size="9">' . $childOrder['shop_name'] . '</font><br/>';

                if ($childOrder['shop_address_line_1'] != '') {
                    $officeAddress .= '<font size="9">' . $childOrder['shop_address_line_1'] . '</font><br/>';
                }

                if ($childOrder['shop_city'] != '') {
                    $officeAddress .= '<font size="9">' . $childOrder['shop_city'] . ', ';
                }

                if ($childOrder['shop_state_name'] != '') {
                    $officeAddress .= $childOrder['shop_state_name'] . '</font><br/>';
                }

                if ($childOrder['shop_country_name'] != '') {
                    $officeAddress .= '<font size="9">' . $childOrder['shop_country_name'] . '</font>';
                }

                $govInfo = nl2br($childOrder['shop_invoice_codes']);
            } else {
                $officeAddress = nl2br(FatApp::getConfig('CONF_ADDRESS_' . $siteLangId, FatUtility::VAR_STRING, ''));
                $govInfo = nl2br(FatApp::getConfig('CONF_GOV_INFO_ON_INVOICE', FatUtility::VAR_STRING, ''));
            }
            ?>
         <tr>
             <table width="100%">
                 <tbody><tr><td><font size="9"><strong><?php echo Labels::getLabel('LBL_Regd._Office', $siteLangId); ?>:</strong><?php echo $officeAddress; ?></font>
                         </td>
                         <?php
                            $site_conatct = FatApp::getConfig('CONF_SITE_PHONE_CODE', FatUtility::VAR_STRING, '') . ' ' . FatApp::getConfig('CONF_SITE_PHONE', FatUtility::VAR_STRING, '');
                            $email_id = FatApp::getConfig('CONF_CONTACT_EMAIL', FatUtility::VAR_STRING, '');
                            if ($site_conatct || $email_id) {
                            ?>

                             <td align="right">
                                 <font size="9"><strong><?php echo Labels::getLabel('LBL_Contact', $siteLangId); ?>:</strong> <?php
                                                                                                                                if ($site_conatct) {
                                                                                                                                    echo $site_conatct;
                                                                                                                                }
                                                                                                                                ?>
                                     <?php
                                        if ($email_id) {
                                            echo '|| ' . $email_id;
                                        }
                                        ?></font>
                                 <br>
                                 <font size="9"><strong><?php echo Labels::getLabel('LBL_Other_Details', $siteLangId); ?>:</strong> <?php echo $govInfo; ?></font>
                             </td>
                         <?php } ?>
                     </tr>
                 </tbody>
             </table>
         </tr>
     </tbody>
 </table>