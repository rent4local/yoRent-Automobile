<div class="page">
    <div class="container container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon"><i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Order_Details', $adminLangId); ?></h5>
                            <?php 
                            $breadcrumbData = [];
                            if ($isRentalOrder) { 
                                $breadcrumbData['hrefRental'] = '/rental';
                            }
                            $this->includeTemplate('_partial/header/header-breadcrumb.php', $breadcrumbData);
                            ?>
                        </div>
                    </div>
                </div>


                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_View_Return_Order_Request', $adminLangId); ?></h4>
                        <?php
                        $actionBack = ($requestRow['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT) ? UrlHelper::generateUrl('OrderReturnRequests', 'rental') : UrlHelper::generateUrl('OrderReturnRequests');
                        
                        $data = [
                            'adminLangId' => $adminLangId,
                            'statusButtons' => false,
                            'deleteButton' => false,
                            'otherButtons' => [
                                [
                                    'attr' => [
                                        'href' => $actionBack,
                                        'title' => Labels::getLabel('LBL_BACK', $adminLangId)
                                    ],
                                    'label' => '<i class="fas fa-arrow-left"></i>'
                                ]
                            ]
                        ];

                        $this->includeTemplate('_partial/action-buttons.php', $data, false);
                        ?>
                    </div>
                    <div class="sectionbody">
                        <table class="table table--details">
                            <tr>
                                <td><strong><?php echo Labels::getLabel('LBL_REFERENCE_NUMBER', $adminLangId); ?>:</strong> <?php echo $requestRow["orrequest_reference"] ?></td>
                                <td><strong><?php echo Labels::getLabel('LBL_Product', $adminLangId); ?> : </strong>
                                    <?php
                                    $txt = '';
                                    if ($requestRow['op_selprod_title'] != '') {
                                        $txt .= $requestRow['op_selprod_title'] . '<br/>' . '<small>' . $requestRow['op_product_name'] . '</small>';
                                    } else {
                                        $txt .= $requestRow['op_product_name'];
                                    }
                                    if ($requestRow['op_selprod_options'] != '') {
                                        $txt .= '<br/>' . $requestRow['op_selprod_options'];
                                    }
                                    if ($requestRow['op_brand_name'] != '') {
                                        $txt .= '<br/><strong>' . Labels::getLabel('LBL_Brand', $adminLangId) . ':  </strong> ' . $requestRow['op_brand_name'];
                                    }

                                    if ($requestRow['op_shop_name'] != '') {
                                        $txt .= '<br/><strong>' . Labels::getLabel('LBL_Shop', $adminLangId) . ':  </strong> ' . $requestRow['op_shop_name'];
                                    }
                                    echo $txt;
                                    ?></td>
                                <td><strong><?php echo Labels::getLabel('LBL_Qty', $adminLangId); ?>:</strong> <?php echo $requestRow["orrequest_qty"] ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php echo Labels::getLabel('LBL_Reason', $adminLangId); ?>: </strong> <?php echo $requestRow['orreason_title']; ?></td>
                                <td><strong><?php echo Labels::getLabel('LBL_Date', $adminLangId); ?>: </strong><?php echo FatDate::format($requestRow['orrequest_date'], true); ?></td>
                                <td><strong><?php echo Labels::getLabel('LBL_Status', $adminLangId); ?>:</strong> <?php echo $requestStatusArr[$requestRow['orrequest_status']]; ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php echo Labels::getLabel('LBL_Order_Id/invoice_Number', $adminLangId); ?>: </strong> <?php echo $requestRow['op_invoice_number']; ?></td>
                                <?php if ($requestRow['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT) { ?>
                                <td>
                                    <strong><?php echo Labels::getLabel('LBL_Security_Amount', $adminLangId); ?>: </strong>
                                    <?php echo CommonHelper::displayMoneyFormat(($requestRow['opd_rental_security'] * $requestRow['orrequest_qty']), true, false); ?>
                                </td>
                                <?php } ?>
                                
                                
                                <td><strong><?php echo Labels::getLabel('LBL_Amount', $adminLangId); ?>: </strong>
                                    <?php
                                    $returnDataArr = CommonHelper::getOrderProductRefundAmtArr($requestRow);
                                    echo CommonHelper::displayMoneyFormat($returnDataArr['op_refund_amount'], true, true);		
                                    ?> 
                                </td>
							<?php if(isset($attachedFiles) && !empty($attachedFiles)){ ?>
							<td><strong><?php echo Labels::getLabel('LBL_Download_Attached_Files',$adminLangId); ?>:</strong>
							<?php foreach($attachedFiles as $attachedFile) { ?>
							
							<a href="<?php echo UrlHelper::generateUrl('OrderReturnRequests','downloadAttachedFileForReturn' , array($requestRow["orrequest_id"], 0, $attachedFile['afile_id']));  ?>" class="button small green" title="<?php echo $attachedFile['afile_name']; ?>" > <?php echo Labels::getLabel('LBL_Download',$adminLangId); ?></a> <br />
							<?php } ?>
							</td>
							<?php } ?>
                            </tr>   
                        </table>
                    </div>
                </section>

                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Seller_/_Customer_Details', $adminLangId); ?></h4>
                    </div>
                    <div class="sectionbody">
                        <table class="table bordered rounded">
                            <tr>
                                <th><?php echo Labels::getLabel('LBL_Seller_Details', $adminLangId); ?></th>
                                <th><?php echo Labels::getLabel('LBL_Customer_Details', $adminLangId); ?></th>
                            </tr>
                            <tr>
                                <td><strong><?php echo Labels::getLabel('LBL_Shop_Name', $adminLangId); ?>: </strong><?php echo "<a href='javascript:void(0)' onclick='redirectfunc(\"" . UrlHelper::generateUrl('Shops') . "\", " . $requestRow['op_shop_id'] . ")'>" . $requestRow['op_shop_name'] . "</a>"; ?><br /><strong><?php echo Labels::getLabel('LBL_Name', $adminLangId); ?>: </strong>
                                <?php echo "<a href='javascript:void(0)' onclick='redirectfunc(\"" . UrlHelper::generateUrl('Users') . "\", " . $requestRow['seller_user_id'] . ")'>" . $requestRow['seller_name'] . "</a>"; ?><br /><strong><?php echo Labels::getLabel('LBL_Email_ID', $adminLangId); ?>:</strong> <?php echo $requestRow["seller_email"] ?><br /><strong><?php echo Labels::getLabel('LBL_Phone', $adminLangId); ?>:</strong> <?php echo $requestRow["user_dial_code"] . ' ' . $requestRow["seller_phone"] ?></td>
                                <td><strong><?php echo Labels::getLabel('LBL_Name', $adminLangId); ?>: </strong><?php echo "<a href='javascript:void(0)' onclick='redirectfunc(\"" . UrlHelper::generateUrl('Users') . "\", " . $requestRow['buyer_user_id'] . ")'>" . $requestRow['buyer_name'] . "</a>"; ?><br />
                                    <strong><?php echo Labels::getLabel('LBL_Username', $adminLangId); ?>: </strong><?php echo $requestRow["buyer_username"]; ?><br />
                                    <strong><?php echo Labels::getLabel('LBL_Email_ID', $adminLangId); ?>: </strong> <?php echo $requestRow["buyer_email"] ?><br /><strong><?php echo Labels::getLabel('LBL_Phone', $adminLangId); ?>:</strong> <?php echo $requestRow["user_dial_code"] . ' ' . $requestRow["buyer_phone"] ?></td>
                            </tr>
                        </table>
                    </div>
                </section>

                <?php echo $returnRequestMsgsSrchForm->getFormHtml(); ?>
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Message_Communication', $adminLangId); ?></h4>
                    </div>
                    <div id="loadMoreBtnDiv"></div>
                    <div class="sectionbody" id="messagesList">
                    </div>
                </section>


                <section class="section" id="frmArea">
                    <div class="sectionhead">
                        <h4><?php echo FatApp::getConfig("CONF_WEBSITE_NAME_" . $adminLangId); ?> <?php echo Labels::getLabel('LBL_Says', $adminLangId); ?></h4>
                    </div>
                    <div class="sectionbody space">
                        <?php
                        $frmMsg->setFormTagAttribute('class', 'web_form');
                        $frmMsg->setFormTagAttribute('onSubmit', 'setUpReturnOrderRequestMessage(this); return false;');
                        $frmMsg->developerTags['colClassPrefix'] = 'col-md-';
                        $frmMsg->developerTags['fld_default_col'] = 8;
                        echo $frmMsg->getFormHtml(); ?></div>
                </section>
                <?php if ($requestRow['orrequest_status'] == OrderReturnRequest::RETURN_REQUEST_STATUS_PENDING || $requestRow['orrequest_status'] == OrderReturnRequest::RETURN_REQUEST_STATUS_ESCALATED) { ?>
                    <section class="section" id="frmArea">
                        <div class="sectionhead">
                            <h4><?php echo Labels::getLabel('LBL_Update_Status', $adminLangId); ?></h4>
                        </div>
                        <div class="sectionbody space">
                            <?php
                            $frmUpdateStatus->setFormTagAttribute('class', 'web_form');
                            $frmUpdateStatus->setFormTagAttribute('onSubmit', 'setupStatus(this); return false;');
                            $frmUpdateStatus->developerTags['colClassPrefix'] = 'col-md-';
                            $frmUpdateStatus->developerTags['fld_default_col'] = 8;

                            $frmUpdateStatus->getField('orrequest_status')->setFieldTagAttribute('id', 'orrequest_status');
                            /* $frmUpdateStatus->getField('orrequest_refund_in_wallet')->setFieldTagAttribute('id', 'orrequest_refund_in_wallet');

                            $frmUpdateStatus->getField('orrequest_refund_in_wallet')->setWrapperAttribute('class', 'wrapper-orrequest_refund_in_wallet hide'); */
                            $frmUpdateStatus->getField('orrequest_admin_comment')->setWrapperAttribute('class', 'wrapper-orrequest_admin_comment hide');

                            echo $frmUpdateStatus->getFormHtml(); ?>
                        </div>
                    </section>
                    <?php /* <script language="javascript">
                        $(document).ready(function() {
                            $('#orrequest_refund_in_wallet').change(function() {
                                if ($(this).is(':checked')) {
                                    $('.wrapper-orrequest_admin_comment').removeClass('hide');
                                } else {
                                    $('.wrapper-orrequest_admin_comment').addClass('hide');
                                }
                            });
                            <?php if ($requestRow["orrequest_type"] == OrderReturnRequest::RETURN_REQUEST_TYPE_REFUND) { ?>
                                $('#orrequest_status').change(function() {
                                    if ($(this).val() === '2') {
                                        $('.wrapper-orrequest_refund_in_wallet').removeClass('hide');
                                        $('#orrequest_refund_in_wallet').change();
                                    } else {
                                        $('.wrapper-orrequest_admin_comment').addClass('hide');
                                        $('.wrapper-orrequest_refund_in_wallet').addClass('hide');
                                    }
                                });
                            <?php } ?>
                        });
                    </script> */ ?>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
