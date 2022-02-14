<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
if (!$print) {
    $activeAction = ($request['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT) ? "rentalorderreturnrequests" : "orderreturnrequests";
    $navData = [
        'action' => $activeAction, 
        'controller' => 'buyer'
    ];
    $this->includeTemplate('_partial/dashboardNavigation.php', ['navData' => $navData]);
}
?>
<main id="main-area" class="main">
    <div class="content-wrapper content-space">
        <?php if (!$print) { ?>
            <div class="content-header row">
                <div class="col">
                    <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                    <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_View_Order_Return_Request', $siteLangId) . ': <span class="number">' . $request['orrequest_reference'] . '</span>'; ?></h2>
                </div>
            </div>
        <?php } ?>
        <div class="content-body">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><?php echo Labels::getLabel('LBL_Request_Details', $siteLangId); ?></h5>
                    <?php if (!$print) { ?>
                        <div class="">
                            <?php $url = ($prodType == applicationConstants::PRODUCT_FOR_RENT) ? UrlHelper::generateUrl('Buyer', 'rentalOrderReturnRequests') : UrlHelper::generateUrl('Buyer', 'orderReturnRequests'); ?>
                            <iframe src="<?php echo Fatutility::generateUrl('buyer', 'viewOrderReturnRequest', $urlParts) . '/print'; ?>" name="frame" style="display:none"></iframe>
                            <?php /* <a href="javascript:void(0)" onclick="frames['frame'].print()" class="btn btn-brand btn-sm no-print"><?php echo Labels::getLabel('LBL_Print', $siteLangId); ?></a> */ ?>
                            <a href="<?php echo $url; ?>" class="btn btn-outline-brand btn-sm no-print"><?php echo Labels::getLabel('LBL_Back', $siteLangId); ?></a>
                        </div>
                    <?php } ?>
                </div>
                <div class="card-body ">
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-6  mb-4">
                        <div class="bg-gray p-3 rounded">
                            <div class="info--order">
                                <h5><?php echo Labels::getLabel('LBL_Vendor_Return_Address', $siteLangId); ?></h5>
                                <?php echo ($vendorReturnAddress['ura_name'] != null) ? '<h6>' . $vendorReturnAddress['ura_name'] . '</h6>' : ''; ?>
                                <p>
                                    <?php echo (strlen($vendorReturnAddress['ura_address_line_1']) > 0) ? $vendorReturnAddress['ura_address_line_1'] . '<br/>' : ''; ?>
                                    <?php echo (strlen($vendorReturnAddress['ura_address_line_2']) > 0) ? $vendorReturnAddress['ura_address_line_2'] . '<br>' : ''; ?>
                                    <?php echo (strlen($vendorReturnAddress['ura_city']) > 0) ? $vendorReturnAddress['ura_city'] . ',' : ''; ?>
                                    <?php echo (strlen($vendorReturnAddress['state_name']) > 0) ? $vendorReturnAddress['state_name'] . '<br>' : ''; ?>
                                    <?php echo (strlen($vendorReturnAddress['country_name']) > 0) ? $vendorReturnAddress['country_name'] . '<br>' : ''; ?>
                                    <?php echo (strlen($vendorReturnAddress['ura_zip']) > 0) ? Labels::getLabel('LBL_Zip:', $siteLangId) . $vendorReturnAddress['ura_zip'] . '<br>' : ''; ?>
                                    <?php echo (strlen($vendorReturnAddress['ura_phone']) > 0) ? Labels::getLabel('LBL_Phone:', $siteLangId) . $vendorReturnAddress['ura_phone'] . '<br>' : ''; ?>
                                </p>
                            </div>
                        </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-6  mb-4">
                        <div class="bg-gray p-3 rounded">
                            <div class="info--order">
                                <h5><?php echo Labels::getLabel('LBL_Vendor_Detail', $siteLangId); ?></h5>
                                <p>
                                    <?php echo ($request['op_shop_owner_name'] != '') ? '<strong>' . Labels::getLabel('LBL_Vendor_Name', $siteLangId) . ':</strong> ' . $request['op_shop_owner_name'] : ''; ?></p>
                                <p>
                                    <?php
                                    /* $vendorShopUrl = UrlHelper::generateUrl('Shops', 'View', array($request['op_shop_id'])); */
                                    echo ($request['op_shop_name'] != '') ? '<strong>' . Labels::getLabel('LBL_Shop_Name', $siteLangId) . ':</strong> ' . $request['op_shop_name'] . '<br/>' : '';
                                    ?>
                                </p>
                                <span class="gap"></span>
                            </div>
                        </div>
                        </div>
                    </div>
                    <?php if ($canEscalateRequest && !$print) { ?>
                        <a class="btn btn-brand no-print" onClick="javascript: return confirm('<?php echo Labels::getLabel('MSG_Do_you_want_to_proceed?', $siteLangId); ?>')" href="<?php echo UrlHelper::generateUrl('Account', 'escalateOrderReturnRequest', array($request['orrequest_id'])); ?>"><?php echo str_replace("{websitename}", FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId), Labels::getLabel('LBL_Escalate_to_{websitename}', $siteLangId)); ?></a>
                    <?php } ?>

                    <?php if ($canWithdrawRequest && !$print) { ?>
                        <a class="btn btn-brand btn-sm no-print" onClick="javascript: return confirm('<?php echo Labels::getLabel('MSG_Do_you_want_to_proceed?', $siteLangId); ?>')" href="<?php echo UrlHelper::generateUrl('Buyer', 'WithdrawOrderReturnRequest', array($request['orrequest_id'])); ?>"><?php echo Labels::getLabel('LBL_Withdraw_Request', $siteLangId); ?></a>
                    <?php } ?>


                    <?php if (!empty($request)) { ?>
                        <div class="scroll scroll-x js-scrollable table-wrap">
                            <table class="table">
                                <tbody>
                                    <tr class="">
                                        <th width="15%"><?php echo Labels::getLabel('LBL_ID', $siteLangId); ?></th>
                                        <th width="20%"><?php echo Labels::getLabel('LBL_Order_Id/Invoice_Number', $siteLangId); ?></th>
                                        <th><?php echo Labels::getLabel('LBL_Product', $siteLangId); ?></th>
                                        <th width="15%"><?php echo Labels::getLabel('LBL_Return_Qty', $siteLangId); ?></th>
                                        <th width="15%"><?php echo Labels::getLabel('LBL_Request_Type', $siteLangId); ?></th>
                                    </tr>
                                    <tr>
                                        <td><?php echo $request['orrequest_reference'] /* CommonHelper::formatOrderReturnRequestNumber($request['orrequest_id']) */; ?></td>
                                        <td><?php echo $request['op_invoice_number']; ?>
                                        </td>
                                        <td>
                                            <div class="item__description">
                                                <?php if ($request['op_selprod_title'] != '') { ?>
                                                    <div class="item__title" title="<?php echo $request['op_selprod_title']; ?>"><?php echo $request['op_selprod_title']; ?></div>
                                                    <div class="item__sub_title"><?php echo $request['op_product_name']; ?></div>
                                                <?php } else { ?>
                                                    <div class="item__title" title="<?php echo $request['op_product_name']; ?>"><?php echo $request['op_product_name']; ?></div>
                                                <?php } ?>
                                                <?php if (!empty($request['op_brand_name'])) { ?>
                                                    <div class="item__brand"><?php echo Labels::getLabel('LBL_Brand', $siteLangId); ?>: <?php echo $request['op_brand_name']; ?></div>
                                                <?php } ?>
                                                <?php if ($request['op_selprod_options'] != '') { ?>
                                                    <div class="item__specification"><?php echo $request['op_selprod_options']; ?></div>
                                                <?php } ?>

                                                <?php if ($request['op_selprod_sku'] != '') { ?>
                                                    <div class="item__sku"><?php echo Labels::getLabel('LBL_SKU', $siteLangId) . ':  ' . $request['op_selprod_sku']; ?> </div>
                                                <?php } ?>

                                                <?php if ($request['op_product_model'] != '') { ?>
                                                    <div class="item__model"><?php echo Labels::getLabel('LBL_Model', $siteLangId) . ':  ' . $request['op_product_model']; ?></div>
                                                <?php } ?>
                                            </div>
                                        </td>
                                        <td><?php echo $request['orrequest_qty']; ?></td>
                                        <td> <?php echo $returnRequestTypeArr[$request['orrequest_type']]; ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="scroll scroll-x js-scrollable table-wrap">
                            <table class="table">
                                <tbody>
                                    <tr class="">
                                        <th width="20%"><?php echo Labels::getLabel('LBL_Reason', $siteLangId); ?></th>
                                        <th width="20%"><?php echo Labels::getLabel('LBL_Date', $siteLangId); ?></th>
                                        <th width="20%"><?php echo Labels::getLabel('LBL_Status', $siteLangId); ?></th>
                                        <?php if ($request['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT) { ?>
                                            <th width="15%"><?php echo Labels::getLabel('LBL_Security_Amount', $siteLangId); ?></th>
                                        <?php } ?>
                                        <th width="20%"><?php echo Labels::getLabel('LBL_Amount', $siteLangId); ?></th>
                                        <?php if (isset($attachedFiles) && !empty($attachedFiles)) { ?>
                                            <th width="20%"><?php echo Labels::getLabel('LBL_Download_Attached_Files', $siteLangId); ?></th>
                                        <?php } ?>
                                    </tr>
                                    <tr>
                                        <td><?php echo $request['orreason_title']; ?></td>
                                        <td>
                                            <div class="item__description">
                                                <span class=""><?php echo FatDate::format($request['orrequest_date']); ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo $requestRequestStatusArr[$request['orrequest_status']]; ?></td>
                                        
                                        <?php if ($request['opd_sold_or_rented'] == applicationConstants::ORDER_TYPE_RENT) { ?>
                                            <td>
                                                <?php echo CommonHelper::displayMoneyFormat(($request['opd_rental_security'] * $request['orrequest_qty']), true, false); ?>
                                            </td>
                                        <?php } ?>
                                        
                                        <td><?php
                                            $returnDataArr = CommonHelper::getOrderProductRefundAmtArr($request);
                                            echo CommonHelper::displayMoneyFormat($returnDataArr['op_refund_amount'], true, false);
                                            ?>
                                        </td>
                                        <?php if (isset($attachedFiles) && !empty($attachedFiles)) {
											echo "<td>";
										foreach($attachedFiles as $attachedFile) { ?>
											<p><a href="<?php echo UrlHelper::generateUrl('Buyer', 'downloadAttachedFileForReturn', array($request["orrequest_id"], 0, $attachedFile['afile_id'])); ?>" class="button small green" title="<?php echo $attachedFile['afile_name'];?>"> <?php echo Labels::getLabel('LBL_Download', $siteLangId); ?></a></p>
											
										<?php }
										echo "</td>";
										?>
                                            
                                        <?php } ?>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>
                    <?php if (!$print) { ?>
                        <div class="no-print">
                            <?php echo $returnRequestMsgsSrchForm->getFormHtml(); ?>
                            <div class="gap"></div>
                            <h5><?php echo Labels::getLabel('LBL_Return_Request_Messages', $siteLangId); ?> </h5>
                            <div id="loadMoreBtnDiv"></div>
                            <ul class="messages-list" id="messagesList"></ul>

                            <div class="gap"></div>
                            <?php
                            if ($request && ($request['orrequest_status'] != OrderReturnRequest::RETURN_REQUEST_STATUS_REFUNDED && $request['orrequest_status'] != OrderReturnRequest::RETURN_REQUEST_STATUS_WITHDRAWN)) {
                                $frmMsg->setFormTagAttribute('onSubmit', 'setUpReturnOrderRequestMessage(this); return false;');
                                $frmMsg->setFormTagAttribute('class', 'form');
                                $frmMsg->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
                                $frmMsg->developerTags['fld_default_col'] = 12;
                                $btn = $frmMsg->getField('btn_submit');
                                $btn->addFieldTagAttribute('class', 'btn btn-brand')
                                ?>

                                <div class="messages-list">
                                    <ul>
                                        <li>
                                            <div class="msg_db">
                                                <div class="avtar"><img src="<?php echo UrlHelper::generateUrl('Image', 'user', array($logged_user_id, 'THUMB', 1)); ?>" alt="<?php echo $logged_user_name; ?>" title="<?php echo $logged_user_name; ?>"></div>
                                            </div>
                                            <div class="msg__desc">
                                                <span class="msg__title"><?php echo $logged_user_name; ?></span>

                                                <?php echo $frmMsg->getFormHtml(); ?>


                                            </div>
                                        </li>
                                    </ul>
                                </div>


                            <?php }
                            ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</main>
<?php if ($print) { ?>
    <script>
        $(".sidebar-is-expanded").addClass('sidebar-is-reduced').removeClass('sidebar-is-expanded');
        /*window.print();
         window.onafterprint = function(){
         location.href = history.back();
         }*/
    </script>
<?php } ?>
