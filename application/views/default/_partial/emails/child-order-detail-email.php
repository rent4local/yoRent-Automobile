<table width="100%">
		<tr>
			<td style="padding:15px 0;">
				<table width="100%">
					<tr>
						<td style="padding:15px 0;border-top:1px solid #ddd;border-bottom:1px solid #ddd;"> <strong><?php echo Labels::getLabel('Lbl_Order_Invoice_No.', $siteLangId); ?>:</strong> <span style="font-size: 14px"><?php echo $orderProducts['op_invoice_number']; ?></span> </td>
						<td style="padding:15px 0;text-align:right;border-top:1px solid #ddd;border-bottom:1px solid #ddd;"> <strong><?php echo Labels::getLabel('Lbl_Order_Updated_Date', $siteLangId); ?></strong> : <span style="font-size: 14px"><?php echo  FatDate::format($orderProducts['oshistory_date_added']); ?></span> </td>
					</tr>
					<!-- PRODUCT SECTION START --->
                    <?php 
                    $prodOrBatchUrl = 'javascript:void(0)';
                    $prodOrBatchImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('image', 'product', array($orderProducts['op_selprod_product_id'], "SMALL", $orderProducts['op_selprod_id'], 0, $siteLangId), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg');   
                    $shippingPrice = CommonHelper::orderProductAmount($orderProducts, 'SHIPPING');
                    $productTaxCharged = CommonHelper::orderProductAmount($orderProducts, 'TAX');
                    $totalSecurityAmount = $orderProducts['opd_rental_security'] * $orderProducts['op_qty'];
                    
                    $cartTotal = CommonHelper::orderProductAmount($orderProducts, 'CART_TOTAL');
                    
                    $discountTotal = CommonHelper::orderProductAmount($orderProducts, 'DISCOUNT');
                    $taxCharged = $productTaxCharged;
                    $netAmount = CommonHelper::orderProductAmount($orderProducts, 'NETAMOUNT');
                    
                    $volumeDiscount = CommonHelper::orderProductAmount($orderProducts, 'VOLUME_DISCOUNT');
                    $volumeDiscountTotal = abs(CommonHelper::orderProductAmount($orderProducts, 'VOLUME_DISCOUNT'));
                    
                    $durationDiscount = CommonHelper::orderProductAmount($orderProducts, 'DURATION_DISCOUNT');
                    $durationDiscountTotal = abs(CommonHelper::orderProductAmount($orderProducts, 'DURATION_DISCOUNT'));
                    
                    $rewardPointDiscount =  abs(CommonHelper::orderProductAmount($orderProducts, 'REWARDPOINT'));
                    
                    ?>
					<tr>
						<td colspan="2" style="padding-bottom:20px">
							<table width="100%">
								<tr>
									<td style="padding-bottom:10px">
										<table width="100%" cellspacing="0">
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
                                                                    <a href="<?php echo $prodOrBatchUrl; ?>" style="color: #535b61;text-decoration:none;font-size:18px"><strong><?php echo $orderProducts["op_product_name"]; ?></strong>
                                                                    </a>
                                                                    <?php if (!empty($orderProducts["op_brand_name"])) { ?> 
                                                                        <p style="margin:0;margin-top:10px"><strong><?php echo Labels::getLabel('Lbl_Brand', $siteLangId); ?></strong> : <?php echo $orderProducts["op_brand_name"]; ?></p>
                                                                    <?php } ?>    
																	<p style="margin:0;margin-top:10px"><strong><?php echo Labels::getLabel('Lbl_Sold_By', $siteLangId); ?></strong> : <?php echo $orderProducts["op_shop_name"];?></p>
																</td>
																<td style="background-color: #efefef;text-align:center;padding: 10px;"> <strong><?php echo  CommonHelper::displayMoneyFormat($orderProducts["op_unit_price"]); ?></strong> </td>
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
                                                                                <?php echo $orderProducts['op_qty']; ?>
                                                                            </td>
                                                                        </tr>
                                                                        <?php if ($orderProducts['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT) { 
                                                                            $rentDuration = CommonHelper::getDifferenceBetweenDates($orderProducts['opd_rental_start_date'], $orderProducts['opd_rental_end_date'], $orderProducts['op_selprod_user_id'], $orderProducts['opd_rental_type']);
                                                                            ?>
                                                                            <tr>
                                                                                <td align="" width="50%">
                                                                                    <?php echo Labels::getLabel('Lbl_From', $siteLangId); ?>
                                                                                </td>
                                                                                <td align="center" width="5%"><strong>:</strong></td>
                                                                                <td align="right" width="45%" style="font-size: 15px">
                                                                                    <?php echo date('M d, Y ', strtotime($orderProducts['opd_rental_start_date'])); ?>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td align="" width="50%">
                                                                                    <?php echo Labels::getLabel('Lbl_To', $siteLangId); ?>
                                                                                </td>
                                                                                <td align="center" width="5%"><strong>:</strong></td>
                                                                                <td align="right" width="45%" style="font-size: 15px">
                                                                                    <?php echo date('M d, Y ', strtotime($orderProducts['opd_rental_end_date'])); ?>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td align="" width="50%">
                                                                                    <?php echo Labels::getLabel('Lbl_Duration', $siteLangId);?>
                                                                                </td>
                                                                                <td align="center" width="5%"><strong>:</strong></td>
                                                                                <td align="right" width="45%" style="font-size: 15px">
                                                                                    <?php echo CommonHelper::displayProductRentalDuration($rentDuration, $orderProducts['opd_rental_type'], $siteLangId); ?>
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
                                                                        <?php if ($shippingPrice > 0) { ?>
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
                                                                        <?php if ($productTaxCharged > 0) { ?>
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
                                                        <?php if ($shippingPrice > 0) { ?>
														<tr>
															<td width="50%"><strong><?php echo Labels::getLabel('LBL_SHIPPING', $siteLangId);?></strong></td>
															<td width="45%" style="text-align:right;"><?php echo CommonHelper::displayMoneyFormat($shippingPrice);?></td>
														</tr>
                                                        <?php } ?>
                                                        <?php if ($taxCharged > 0) { ?>
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
                                                        <?php if (array_key_exists('op_rounding_off', $orderProducts) && 0 != $orderProducts['op_rounding_off']) { 
                                                        $roundingLabel = (0 < $orderProducts['op_rounding_off']) ? Labels::getLabel('LBL_Rounding_Up', $siteLangId) : Labels::getLabel('LBL_Rounding_Down', $siteLangId);    
                                                        ?>
                                                        <tr>
															<td width="50%"><strong><?php echo $roundingLabel; ?></strong></td>
															<td width="45%" style="text-align:right;"><?php echo CommonHelper::displayMoneyFormat($orderProducts['op_rounding_off']);?></td>
														</tr>
                                                        <?php } ?>
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
                </table>
			</td>
		</tr>
	</table>