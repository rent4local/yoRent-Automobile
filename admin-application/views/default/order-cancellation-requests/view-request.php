<section class="section">
    <div class="sectionhead">

        <h4><?php echo Labels::getLabel('LBL_Cancellation_Request', $adminLangId); ?>
        </h4>
    </div>
    <div class="sectionbody space">
        <div class="border-box border-box--space">
            <div class="row space">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h5><?php echo Labels::getLabel('LBL_Buyer_Details', $adminLangId); ?></h5>
                    <p><strong><?php echo Labels::getLabel('LBL_NAME', $adminLangId); ?></strong> : <?php echo $data['buyer_name']; ?>
                        <br /><strong><?php echo Labels::getLabel('LBL_USERNAME', $adminLangId); ?></strong> : <?php echo $data['buyer_username']; ?>
                        <br /><strong><?php echo Labels::getLabel('LBL_EMAIL', $adminLangId); ?></strong> : <?php echo $data['buyer_email']; ?>
                        <br /><strong><?php echo Labels::getLabel('LBL_Phone', $adminLangId); ?></strong> : <?php echo $data['buyer_phone']; ?>
                    </p>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h5><?php echo Labels::getLabel('LBL_Seller_Details', $adminLangId); ?></h5>
                    <p><strong><?php echo Labels::getLabel('LBL_NAME', $adminLangId); ?></strong> : <?php echo $data['seller_name']; ?>
                        <br /><strong><?php echo Labels::getLabel('LBL_USERNAME', $adminLangId); ?></strong> : <?php echo $data['seller_username']; ?>
                        <br /><strong><?php echo Labels::getLabel('LBL_EMAIL', $adminLangId); ?></strong> : <?php echo $data['seller_email']; ?>
                        <br /><strong><?php echo Labels::getLabel('LBL_Phone', $adminLangId); ?></strong> : <?php echo $data['seller_phone']; ?>
                    </p>
                </div>
            </div>
            <div class="row space">

                <div class="col-md-12 ">
                    <h5><?php echo Labels::getLabel('LBL_Request_Order_Details', $adminLangId); ?></h5>
                    <table class="table table--hovered table-responsive">
                        <tbody>
                            <tr>
                                <td width="30%"><?php echo Labels::getLabel('LBL_Order/Invoice', $adminLangId); ?>:</td>
                                <td><?php echo $data['op_invoice_number']; ?></td>
                            </tr>
                            <tr>
                                <td width="30%"><?php echo Labels::getLabel('LBL_Order_Status', $adminLangId); ?>:</td>
                                <td><?php echo  $data['orderstatus_name']; ?></td>
                            </tr>
                            <?php
                            $orderTotalAmount = CommonHelper::orderProductAmount($data, 'netamount');
                            $amt = CommonHelper::displayMoneyFormat($orderTotalAmount, true, true); ?>
                            <tr>
                                <td width="30%"><?php echo Labels::getLabel('LBL_Amount', $adminLangId); ?>:</td>
                                <td><?php echo $amt; ?></td>
                            </tr>
                            <tr>
                                <td width="30%"><?php echo Labels::getLabel('LBL_Date', $adminLangId); ?>:</td>
                                <td><?php echo date('M d, Y ', strtotime($data['ocrequest_date'])); ?></td>
                            </tr>

                            <?php
                            if ($data['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT && $data['ocrequest_is_penalty_applicable'] > 0) {
                                $shippingCharges = $data['shipping_charges'];

                                $orderTotalAmount = CommonHelper::orderProductAmount($data, 'netamount') - ($data['opd_rental_security'] * $data['op_qty']) - $shippingCharges;
                                $refunableAmount = ($orderTotalAmount * $data['ocrequest_refund_amount'] / 100) + $shippingCharges;
                            ?>
                                <tr>
                                    <td width="30%"><?php echo Labels::getLabel('LBL_Order_rental_start_date', $adminLangId); ?>:</td>
                                    <td><?php echo date('M d, Y ', strtotime($data['opd_rental_start_date'])); ?></td>
                                </tr>
                                <tr>
                                    <td width="30%"><?php echo Labels::getLabel('LBL_Order_Cancel_Before_Hours', $adminLangId); ?>:</td>
                                    <td><?php echo $data['ocrequest_hours_before_rental']; ?></td>
                                </tr>
                                <tr>
                                    <td width="30%"><?php echo sprintf(Labels::getLabel('LBL_Refundable_Amount(After_Penalty_%s_)', $adminLangId), $data['ocrequest_refund_amount'] . '%'); ?>:</td>
                                    <td><?php echo  CommonHelper::displayMoneyFormat($refunableAmount, true, true) . '<small>(' . Labels::getLabel('LBL_Exc_Security_Amount', $adminLangId) . ')</small>'; ?></td>
                                </tr>
                            <?php
                            }
                            ?>
                            <tr>
                                <td width="30%"><?php echo Labels::getLabel('LBL_Security_(Per_Qty)', $adminLangId); ?>:</td>
                                <td><?php echo CommonHelper::displayMoneyFormat($data['opd_rental_security'], true, true); ?></td>
                            </tr>
                            <tr>
                                <td width="30%"><?php echo Labels::getLabel('LBL_Status', $adminLangId); ?>:</td>
                                <td><?php echo $requestStatusArr[$data['ocrequest_status']]; ?></td>
                            </tr>
                            <tr>
                                <td width="30%"><?php echo Labels::getLabel('LBL_Reason', $adminLangId); ?>:</td>
                                <td><?php echo  $data['ocreason_title']; ?></td>
                            </tr>
                            <tr>
                                <td width="30%"><?php echo Labels::getLabel('LBL_Comments', $adminLangId); ?>:</td>
                                <td><?php echo nl2br($data['ocrequest_message']); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>