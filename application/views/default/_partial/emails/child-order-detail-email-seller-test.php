<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
	<table width="100%">
		<tr>
			<td style="padding:15px 0;">
				<table width="100%">
					<tr>
						<td style="padding:15px 0;border-top:1px solid #ddd;border-bottom:1px solid #ddd;"> <strong><?php echo Labels::getLabel('Lbl_Order_No.', $siteLangId); ?>:</strong> <span style="font-size: 14px"><?php echo $orderDetail['order_id']; ?></span> </td>
						<td style="padding:15px 0;text-align:right;border-top:1px solid #ddd;border-bottom:1px solid #ddd;"> <strong><?php echo Labels::getLabel('Lbl_Order_Date', $siteLangId); ?></strong> <span style="font-size: 14px"><?php echo  FatDate::format($orderDetail['order_date_added']); ?></span></td>
					</tr>
					<!-- PRODUCT SECTION START --->
					<tr>
						<td colspan="2" style="padding-bottom:20px">
							<table width="100%">
								<tr>
									<td style="padding-bottom:10px">
										<table width="100%" cellspacing="0">
											<?php 
                                            $taxCharged = $cartTotal = $total = $shippingTotal = $netAmount = $discountTotal = $volumeDiscountTotal = $durationDiscountTotal = $rewardPointDiscount = $roundingOff = $totalSecurityAmount = $addonCartTotal = 0;
                                            $pickupAddressArr = [];
                                            $isShippingBySeller = false;
                                            $isTaxCollectedBySeller = false;
                                            
                                            foreach ($orderProducts as $prodkey => $val) { 
                                                $prodOrBatchUrl = 'javascript:void(0)';
                                                $prodOrBatchImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('image', 'product', array($val['op_selprod_product_id'], "SMALL", $val['op_selprod_id'], 0, $siteLangId), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg');

                                                $shippingPrice = 0;
                                                if ($val['is_shipping_by_seller']) {
                                                    $isShippingBySeller = true;    
                                                    $shippingPrice = CommonHelper::orderProductAmount($val, 'SHIPPING');
                                                    $shippingTotal += $shippingPrice;
                                                }
                                                
                                                $productTaxCharged = 0;
                                                if ($val['op_tax_collected_by_seller']) {
                                                    $isTaxCollectedBySeller  = true;
                                                    $productTaxCharged = CommonHelper::orderProductAmount($val, 'TAX');
                                                    $taxCharged += $productTaxCharged;
                                                }
                                                
                                                $totalSecurityAmount += $val['opd_rental_security'] * $val['op_qty'];
                                                $cartTotal += CommonHelper::orderProductAmount($val, 'CART_TOTAL');
                                                $discountTotal += CommonHelper::orderProductAmount($val, 'DISCOUNT');
                                                $netAmount += CommonHelper::orderProductAmount($val, 'NETAMOUNT', false, $userType);
                                                
                                                $volumeDiscount = CommonHelper::orderProductAmount($val, 'VOLUME_DISCOUNT');
                                                $volumeDiscountTotal += abs(CommonHelper::orderProductAmount($val, 'VOLUME_DISCOUNT'));
                                                
                                                $durationDiscount = CommonHelper::orderProductAmount($val, 'DURATION_DISCOUNT');
                                                $durationDiscountTotal += abs(CommonHelper::orderProductAmount($val, 'DURATION_DISCOUNT'));
                                                
                                                $rewardPointDiscount +=  abs(CommonHelper::orderProductAmount($val, 'REWARDPOINT'));
                                                $attachedAddon = (isset($addonProductArr[$val['op_id']])) ? $addonProductArr[$val['op_id']] : []; 
                                                
                                                
                                                if (!empty($val['pickupAddress'])) {
                                                $pickupAdd = $val['pickupAddress'];
                                                $pickUpAddressInfo = '<p style="margin:0">'. $pickupAdd['oua_name'] .' - ';
                                                
                                                if ($pickupAdd['oua_address1'] != '') {
                                                    $pickUpAddressInfo .= ' '. $pickupAdd['oua_address1'] . ' ';
                                                }
                                           
                                                if ($pickupAdd['oua_address2'] != '') {
                                                    $pickUpAddressInfo .= ', '. $pickupAdd['oua_address2'];
                                                }
                                                
                                                if ($pickupAdd['oua_city'] != '') {
                                                    $pickUpAddressInfo .=  ', '. $pickupAdd['oua_city'];
                                                }
                                           
                                                if ($pickupAdd['oua_state'] != '') {
                                                    $pickUpAddressInfo .= ', '. $pickupAdd['oua_state'] . ' ';
                                                }
                                           
                                                if ($pickupAdd['oua_zip'] != '') {
                                                    $pickUpAddressInfo .= '-' . $pickupAdd['oua_zip'];
                                                }
                                                
                                                if ($pickupAdd['oua_phone'] != '') {
                                                    $pickUpAddressInfo .= ' ' . $pickupAdd['oua_phone'];
                                                }
                                                $pickUpAddressInfo .= '</p>';
                                                ?>
                                            <tr>
                                                <td colspan="2" style="background-color:#efefef;padding:15px">
                                                    <h6 style="margin:0;font-size:18px;padding-bottom:5px"><?php echo Labels::getLabel('Lbl_Pickup_Address', $siteLangId); ?></h6>
                                                    <p style="margin:0;font-size:14px;color: #535b61;"><?php echo $pickUpAddressInfo; ?></p>
                                                </td>
                                            </tr>
                                            <?php } ?>
                                                
                                                
												<tr>
                                                    <td width="30%" style="vertical-align: top;border-bottom:1px solid #ddd;padding-top:20px;padding-bottom:20px"> 
                                                            <a href="<?php echo $prodOrBatchUrl; ?>">
                                                                <img src="<?php echo $prodOrBatchImgUrl; ?>" style="max-width: 100%;display:block;border: 1px solid #ddd;">
                                                            </a> 
                                                    </td>
													<td width="70%" style="padding-left:25px;border-bottom:1px solid #ddd;padding-top:20px;padding-bottom:20px">
														<table width="100%">
															<tr>
																<td style="color: #535b61;"> 
                                                                    <a href="<?php echo $prodOrBatchUrl; ?>" style="color: #535b61;text-decoration:none;font-size:18px"><strong><?php echo $val["op_product_name"]; ?></strong>
                                                                    </a>
                                                                    <?php if (!empty($val["op_brand_name"])) { ?> 
                                                                        <p style="margin:0;margin-top:10px"><strong><?php echo Labels::getLabel('Lbl_Brand', $siteLangId); ?></strong> : <?php echo $val["op_brand_name"]; ?></p>
                                                                    <?php } ?>    
																	<?php /* <p style="margin:0;margin-top:10px"><strong><?php echo Labels::getLabel('Lbl_Sold_By', $siteLangId); ?></strong> : <?php echo $val["op_shop_name"];?></p> */ ?>
                                                                    <p style="margin:0;margin-top:10px"><strong><?php echo Labels::getLabel('Lbl_Invoice_Number', $siteLangId); ?></strong> : #<?php echo $val["op_invoice_number"];?></p>
																</td>
																<td style="background-color: #efefef;text-align:center;padding: 10px;"> <strong><?php echo  CommonHelper::displayMoneyFormat($val["op_unit_price"]); ?></strong> </td>
															</tr>
															<tr>
																<td colspan="2">
																	<table width="100%" style="border-top: 1px solid #ddd; margin-top: 10px; padding-top: 10px;color:#535b61;line-height:1.4;font-size:14px">
                                                                        <?php if (!empty($attachedAddon)) { 
                                                                        foreach ($attachedAddon as $addon) {    
                                                                            $addonCartTotal += CommonHelper::orderProductAmount($addon, 'CART_TOTAL');
                                                                            $taxCharged += CommonHelper::orderProductAmount($addon, 'TAX');
                                                                            $discountTotal += CommonHelper::orderProductAmount($addon, 'DISCOUNT');
                                                                            $rewardPointDiscount +=  abs(CommonHelper::orderProductAmount($addon, 'REWARDPOINT'));
                                                                            $netAmount += CommonHelper::orderProductAmount($addon, 'NETAMOUNT', false, $userType);
                                                                        ?>
                                                                        <tr>
                                                                            <td width="50%"><span style="background-color:#ffdee3;padding:3px 8px;border-radius:20px;font-size:12px;display:inline-block;font-weight:bold;line-height:1"><?php echo Labels::getLabel('Lbl_Addon', $siteLangId); ?></span> <strong><?php echo $addon["op_product_name"]; ?></strong></td>
                                                                            <td align="center" width="5%"><strong>:</strong></td>
                                                                            <td width="45%" style="font-size: 15px;text-align:right;"><?php echo  CommonHelper::displayMoneyFormat($addon["op_unit_price"]); ?></td>
                                                                        </tr>
                                                                        <?php } 
                                                                        }
                                                                        ?>
                                                                        <tr>
                                                                            <td align="" width="50%">
                                                                                <?php echo Labels::getLabel('Lbl_Qty', $siteLangId); ?>
                                                                            </td>
                                                                            <td align="center" width="5%"><strong>:</strong></td>
                                                                            <td align="right" width="45%" style="font-size: 15px">
                                                                                <?php echo $val['op_qty']; ?>
                                                                            </td> 
                                                                        </tr>
                                                                        <?php if ($val['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT) { 
                                                                            $rentDuration = CommonHelper::getDifferenceBetweenDates($val['opd_rental_start_date'], $val['opd_rental_end_date'], $val['op_selprod_user_id'], $val['opd_rental_type']);
                                                                            ?>
                                                                            <tr>
                                                                                <td align="" width="50%">
                                                                                    <?php echo Labels::getLabel('Lbl_Security_Amount', $siteLangId); ?>
                                                                                </td>
                                                                                <td align="center" width="5%"><strong>:</strong></td>
                                                                                <td align="right" width="45%" style="font-size: 15px">
                                                                                    <?php echo  CommonHelper::displayMoneyFormat($val['opd_rental_security'] * $val['op_qty']); ?>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td align="" width="50%">
                                                                                    <?php echo Labels::getLabel('Lbl_From', $siteLangId); ?>
                                                                                </td>
                                                                                <td align="center" width="5%"><strong>:</strong></td>
                                                                                <td align="right" width="45%" style="font-size: 15px">
                                                                                    <?php echo date('M d, Y ', strtotime($val['opd_rental_start_date'])); ?>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td align="" width="50%">
                                                                                    <?php echo Labels::getLabel('Lbl_To', $siteLangId); ?>
                                                                                </td>
                                                                                <td align="center" width="5%"><strong>:</strong></td>
                                                                                <td align="right" width="45%" style="font-size: 15px">
                                                                                    <?php echo date('M d, Y ', strtotime($val['opd_rental_end_date'])); ?>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td align="" width="50%">
                                                                                    <?php echo Labels::getLabel('Lbl_Duration', $siteLangId);?>
                                                                                </td>
                                                                                <td align="center" width="5%"><strong>:</strong></td>
                                                                                <td align="right" width="45%" style="font-size: 15px">
                                                                                    <?php echo CommonHelper::displayProductRentalDuration($rentDuration, $val['opd_rental_type'], $siteLangId); ?>
                                                                                </td>
                                                                            </tr>
                                                                            <?php if ($durationDiscount != 0) { ?>
                                                                            <tr>
                                                                                <td align="" width="50%">
                                                                                    <?php echo Labels::getLabel('Lbl_Duration_Discount', $siteLangId); ?>
                                                                                </td>
                                                                                <td align="center" width="5%"><strong>:</strong></td>
                                                                                <td align="right" width="45%" style="font-size: 15px">
                                                                                    <?php echo  CommonHelper::displayMoneyFormat($durationDiscount); ?>
                                                                                </td>
                                                                            </tr>
                                                                            <?php } ?>
                                                                            <?php } ?>
                                                                        <?php if ($volumeDiscount != 0) { ?>
                                                                            <tr>
                                                                                <td align="" width="50%">
                                                                                    <?php echo Labels::getLabel('Lbl_Volumne_Discount', $siteLangId); ?>
                                                                                </td>
                                                                                <td align="center" width="5%"><strong>:</strong></td>
                                                                                <td align="right" width="45%" style="font-size: 15px">
                                                                                    <?php echo CommonHelper::displayMoneyFormat($volumeDiscount); ?>
                                                                                </td>
                                                                            </tr>
                                                                        <?php } ?>
                                                                        <?php if ($shippingPrice > 0 && $val['is_shipping_by_seller']) { ?>
                                                                        <tr>
                                                                            <td align="" width="50%">
                                                                                <?php echo Labels::getLabel('Lbl_Shipping', $siteLangId); ?>
                                                                            </td>
                                                                            <td align="center" width="5%"><strong>:</strong></td>
                                                                            <td align="right" width="45%" style="font-size: 15px">
                                                                                <?php echo CommonHelper::displayMoneyFormat($shippingPrice); ?>
                                                                            </td>
                                                                        </tr>
                                                                        <?php } ?>
                                                                        <?php if ($productTaxCharged > 0 && $val['op_tax_collected_by_seller']) { ?>
                                                                        <tr>
                                                                            <td align="" width="50%">
                                                                                <?php echo Labels::getLabel('Lbl_Tax', $siteLangId);?>
                                                                            </td>
                                                                            <td align="center" width="5%"><strong>:</strong></td>
                                                                            <td align="right" width="45%" style="font-size: 15px">
                                                                                <?php echo CommonHelper::displayMoneyFormat($productTaxCharged); ?>
                                                                            </td>
                                                                        </tr>
                                                                        <?php } ?>
																	</table>
																</td>
															</tr>
														</table>
													</td>
												</tr>
												<!--repeated row for reported order-->
												
                                            <?php } ?>
                                        </table>
									</td>
								</tr>
								<!-- PRODUCT SECTION END -->
								<tr>
									<td style="background-color: #f9f9f9;">
										<table width="100%">
											<tr>
												<td width="30%"></td>
												<td width="70%" style="padding:20px 25px;">
													<table width="100%" style="color:#535b61;line-height:1.4">
														<tr>
															<td width="50%"><strong><?php echo Labels::getLabel('L_CART_TOTAL_(_QTY_*_Product_price_)', $siteLangId) ;?></strong></td>
															<td width="45%" style="text-align:right;"><?php echo CommonHelper::displayMoneyFormat($cartTotal); ?></td>
														</tr>
                                                        <?php if ($totalSecurityAmount > 0) { ?>
														<tr>
															<td width="50%"><strong><?php echo Labels::getLabel('LBL_Total_Rental_Security_Amount', $siteLangId);?></strong></td>
															<td width="45%" style="text-align:right;"><?php echo  CommonHelper::displayMoneyFormat($totalSecurityAmount);?></td>
														</tr>
                                                        <?php } ?>
                                                        <?php if ($addonCartTotal > 0) { ?>
                                                        <tr>
															<td width="50%"><strong><?php echo Labels::getLabel('LBL_Addon_Amount', $siteLangId);?></strong></td>
															<td width="45%" style="text-align:right;"><?php echo CommonHelper::displayMoneyFormat($addonCartTotal);?></td>
														</tr>
                                                        <?php } ?>
                                                        
                                                        <?php if ($discountTotal != 0) { ?>
                                                        <tr>
															<td width="50%"><strong><?php echo Labels::getLabel('LBL_Coupon_Discount', $siteLangId); ?></strong></td>
															<td width="45%" style="text-align:right;"><?php echo CommonHelper::displayMoneyFormat($discountTotal);?></td>
														</tr>
                                                        <?php } ?>
                                                        <?php if ($volumeDiscountTotal != 0) { ?>
                                                        <tr>
															<td width="50%"><strong><?php echo Labels::getLabel('LBL_Volumne_Discount', $siteLangId); ?></strong></td>
															<td width="45%" style="text-align:right;"><?php echo CommonHelper::displayMoneyFormat($volumeDiscountTotal);?></td>
														</tr>
                                                        <?php } ?>
                                                        <?php if ($durationDiscountTotal != 0) { ?>
                                                        <tr>
															<td width="50%"><strong><?php echo Labels::getLabel('LBL_Duration_Discount', $siteLangId); ?></strong></td>
															<td width="45%" style="text-align:right;"><?php echo CommonHelper::displayMoneyFormat($durationDiscountTotal);?></td>
														</tr>
                                                        <?php } ?>
                                                        <?php if ($shippingTotal > 0 && $isShippingBySeller) { ?>
														<tr>
															<td width="50%"><strong><?php echo Labels::getLabel('LBL_SHIPPING', $siteLangId);?></strong></td>
															<td width="45%" style="text-align:right;"><?php echo CommonHelper::displayMoneyFormat($shippingTotal);?></td>
														</tr>
                                                        <?php } ?>
                                                        <?php if ($taxCharged > 0 && $isTaxCollectedBySeller) { ?>
														<tr>
															<td width="50%"><strong><?php echo Labels::getLabel('LBL_Tax', $siteLangId); ?></strong></td>
															<td width="45%" style="text-align:right;"><?php echo CommonHelper::displayMoneyFormat($taxCharged);?></td>
														</tr>
                                                        <?php } ?>
                                                        <?php if ($rewardPointDiscount != 0) { ?>
                                                        <tr>
															<td width="50%"><strong><?php echo Labels::getLabel('LBL_Reward_Point_Discount', $siteLangId); ?></strong></td>
															<td width="45%" style="text-align:right;"><?php echo CommonHelper::displayMoneyFormat($rewardPointDiscount);?></td>
														</tr>
                                                        <?php } ?>
                                                        <?php /*if (array_key_exists('order_rounding_off', $orderInfo) && 0 != $orderInfo['order_rounding_off']) { 
                                                        $roundingLabel = (0 < $orderInfo['order_rounding_off']) ? Labels::getLabel('LBL_Rounding_Up', $siteLangId) : Labels::getLabel('LBL_Rounding_Down', $siteLangId);    
                                                        ?>
                                                        <tr>
															<td width="50%"><strong><?php echo $roundingLabel; ?></strong></td>
															<td width="45%" style="text-align:right;"><?php echo CommonHelper::displayMoneyFormat($orderInfo['order_rounding_off']);?></td>
														</tr>
                                                        <?php }*/ ?>
                                                        <tr>
															<td width="50%" style="font-size: 18px"><strong><?php echo Labels::getLabel('LBL_ORDER_TOTAL', $siteLangId); ?></strong></td>
															<td width="45%" style="text-align:right;"><strong><?php echo CommonHelper::displayMoneyFormat($netAmount); ?></strong></td>
														</tr>
													</table>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</td>
					</tr>
                    <?php 
                    /* if (!empty($orderProducts['pickupAddress'])) {
                        $pickUpAddressInfo = '<p style="margin:0">'. $orderProducts['pickupAddress']['oua_name'] . '</p>';
                        $pickUpAddressInfo .= '<p style="margin:0">';
                        if ($orderProducts['pickupAddress']['oua_address1'] != '') {
                            $pickUpAddressInfo .=  $orderProducts['pickupAddress']['oua_address1'] . ' ';
                        }
                
                        if ($orderProducts['pickupAddress']['oua_address2'] != '') {
                            $pickUpAddressInfo .= '<p style="margin:0">'. $orderProducts['pickupAddress']['oua_address2'];
                        }
                        $pickUpAddressInfo .= '</p>';
                        $pickUpAddressInfo .= '<p style="margin:0">';
                        if ($orderProducts['pickupAddress']['oua_city'] != '') {
                            $pickUpAddressInfo .=  $orderProducts['pickupAddress']['oua_city'] . ', ';
                        }
                
                        if ($orderProducts['pickupAddress']['oua_state'] != '') {
                            $pickUpAddressInfo .= $orderProducts['pickupAddress']['oua_state'] . ', ';
                        }
                
                        if ($orderProducts['pickupAddress']['oua_zip'] != '') {
                            $pickUpAddressInfo .= '-' . $orderProducts['pickupAddress']['oua_zip'];
                        }
                        $pickUpAddressInfo .= '</p>';
                
                        if ($orderProducts['pickupAddress']['oua_phone'] != '') {
                            $pickUpAddressInfo .= '<p style="margin:0">' . $orderProducts['pickupAddress']['oua_phone']. '</p>';
                        }
                    } */   
                    
                    $billingInfo = '<p style="margin:0">'.$billingAddress['oua_name'] . '<p>';
                    $billingInfo .= '<p style="margin:0">';
                    if ($billingAddress['oua_address1'] != '') {
                        $billingInfo .= $billingAddress['oua_address1'] . ' ';
                    }
                   
                    if ($billingAddress['oua_address2'] != '') {
                        $billingInfo .= ', '. $billingAddress['oua_address2'];
                    }
                    $billingInfo .= '</p>';
                    $billingInfo .= '<p style="margin:0">';
                    
                    if ($billingAddress['oua_city'] != '') {
                        $billingInfo .= $billingAddress['oua_city'] . ', ';
                    }
                    
                    if ($billingAddress['oua_zip'] != '') {
                        $billingInfo .= $billingAddress['oua_state'];
                    }
                    
                    if ($billingAddress['oua_country'] != '') {
                        $billingInfo .= ' '. $billingAddress['oua_country'];
                    } else {
                        $billingInfo .= ' '.$billingAddress['oua_country_code'];
                    }
                    
                    if ($billingAddress['oua_zip'] != '') {
                        $billingInfo .= '-' . $billingAddress['oua_zip'];
                    }
                    $billingInfo .= '</p>';
                    
                    if ($billingAddress['oua_phone'] != '') {
                        $billingInfo .= '<p style="margin:0">' . $billingAddress['oua_phone']. '</p>';
                    }
                    
                    
                    $shippingInfo = '';
                    if (!empty($shippingAddress)) {
                        $shippingInfo = '<p style="margin:0">'.$shippingAddress['oua_name'] . '<p>';
                        $shippingInfo .= '<p style="margin:0">';
                        if ($shippingAddress['oua_address1'] != '') {
                            $shippingInfo .= $shippingAddress['oua_address1'] . ' ';
                        }
                        
                        if ($shippingAddress['oua_address2'] != '') {
                            $shippingInfo .= ', '. $shippingAddress['oua_address2'];
                        }
                        $shippingInfo .= '</p>';
                        $shippingInfo .= '<p style="margin:0">';
                        
                        if ($shippingAddress['oua_city'] != '') {
                            $shippingInfo .= $shippingAddress['oua_city'] . ', ';
                        }
                        
                        if ($shippingAddress['oua_state'] != '') {
                            $shippingInfo .= $shippingAddress['oua_state'];
                        }
                        
                        if ($shippingAddress['oua_country'] != '') {
                            $shippingInfo .= ' '. $shippingAddress['oua_country'];
                        } else {
                            $shippingInfo .= ' '.$shippingAddress['oua_country_code'];
                        }
                        
                        if ($shippingAddress['oua_zip'] != '') {
                            $shippingInfo .= '-' . $shippingAddress['oua_zip'];
                        }
                        $shippingInfo .= '</p>';
                        
                        if ($shippingAddress['oua_phone'] != '') {
                            $shippingInfo .= '<p style="margin:0">' . $shippingAddress['oua_phone']. '</p>';
                        }
                    }
                    
                    ?>
                    <tr>
						<td colspan="2" style="border-bottom:1px solid #ddd;border-top:1px solid #ddd;padding-bottom:20px;padding-top:20px">
							<table width="100%">
								<tr>
									<td style="color: #535b61;font-size:14px;padding-right:40px;line-height:1.6">
										<h3 style="margin:0"><?php echo Labels::getLabel('LBL_Order_Billing_Details', $siteLangId); ?></h3>
										<?php echo $billingInfo; ?>
									</td>
                                    <?php if (trim($shippingInfo) != '') { ?>
									<td style="color: #535b61;font-size:14px;text-align:right;padding-left:40px;line-height:1.6">
										<h3 style="margin:0"><?php echo Labels::getLabel('LBL_Order_Shipping_Details', $siteLangId); ?></h3>
										<?php echo $shippingInfo; ?>
									</td>
                                    <?php } elseif (!empty($pickupAddressArr)) { ?>
                                    <td style="color: #535b61;font-size:14px;text-align:right;padding-left:40px;line-height:1.6">
                                        <h3 style="margin:0"><?php echo Labels::getLabel('LBL_Pickup_Address', $siteLangId); ?></h3>
                                        <?php foreach ($pickupAddressArr as $pickupAdd) {
                                            $pickUpAddressInfo = '<p style="margin:0">'. $pickupAdd['oua_name'] . '</p>';
                                            $pickUpAddressInfo .= '<p style="margin:0">';
                                            if ($pickupAdd['oua_address1'] != '') {
                                                $pickUpAddressInfo .=  $pickupAdd['oua_address1'] . ' ';
                                            }
                                    
                                            if ($pickupAdd['oua_address2'] != '') {
                                                $pickUpAddressInfo .= '<p style="margin:0">'. $pickupAdd['oua_address2'];
                                            }
                                            $pickUpAddressInfo .= '</p>';
                                            $pickUpAddressInfo .= '<p style="margin:0">';
                                            if ($pickupAdd['oua_city'] != '') {
                                                $pickUpAddressInfo .=  $pickupAdd['oua_city'] . ', ';
                                            }
                                    
                                            if ($pickupAdd['oua_state'] != '') {
                                                $pickUpAddressInfo .= $pickupAdd['oua_state'] . ', ';
                                            }
                                    
                                            if ($pickupAdd['oua_zip'] != '') {
                                                $pickUpAddressInfo .= '-' . $pickupAdd['oua_zip'];
                                            }
                                            $pickUpAddressInfo .= '</p>';
                                    
                                            if ($pickupAdd['oua_phone'] != '') {
                                                $pickUpAddressInfo .= '<p style="margin:0; border-bottom: 1px solid #dcdcdc;">' . $pickupAdd['oua_phone']. '</p>';
                                            }
                                            echo $pickUpAddressInfo .'';                    
                                        }
                                    } ?>
                                    </td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>