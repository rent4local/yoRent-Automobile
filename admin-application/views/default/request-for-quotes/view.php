<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$rfqId = $rfqData['rfq_id'];
$imageType = array('png', 'jpeg', 'jpg', 'gif', 'bmp', 'ico', 'tiff', 'tif');
$rentalDurArr = ProductRental::durationTypeArr($adminLangId);
?>
<div class="page">
    <div class="container container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon"><i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_RFQ_Detail', $adminLangId); ?></h5>
                            <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php //echo Labels::getLabel('LBL_Buyer_Detail', $adminLangId); ?> <?php echo $rfqData['buyer_name']; ?></h4>

                        <?php
                        $data = [
                            'adminLangId' => $adminLangId,
                            'statusButtons' => false,
                            'deleteButton' => false,
                            'otherButtons' => [
                                [
                                    'attr' => [
                                        'href' => CommonHelper::generateUrl('requestForQuotes'),
                                        'title' => Labels::getLabel('LBL_BACK', $adminLangId)
                                    ],
                                    'label' => '<i class="fas fa-arrow-left"></i>'
                                ],
                            ]
                        ];
                        $extraButtons = array();
                        if (true === RequestForQuote::canAdminUpdateStatus($rfqData['rfq_status'])) {
                            $extraButtons = [
                                [
                                    'attr' => [
                                        'href' => 'javascript:void(0)',
                                        'title' => Labels::getLabel('LBL_Close_RFQ', $adminLangId),
                                        'onclick' => 'changeRfqStatus("' . $rfqId . '")',
                                    ],
                                    'label' => '<i class="fas fa fa-times"></i>'
                                ]
                            ];
                        }

                        $data['otherButtons'] = array_merge($data['otherButtons'], $extraButtons);
                        $this->includeTemplate('_partial/action-buttons.php', $data, false);
                        ?>
                    </div>
                    <!-- <div class="sectionbody">
                        <table class="table table--two-cols no-border-row table-simple">
                            <tbody><tr>
                                    <th><?php //echo Labels::getLabel('LBL_Name', $adminLangId); ?>: </th>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div> -->
                </section>

                <div class="row row--cols-group">
                    <div class="col-lg-6 col-md-6 col-sm-6">
                        <section class="section">
                            <div class="sectionhead">
                                <h4><?php echo Labels::getLabel('LBL_Buyer_Original_Offer', $adminLangId); ?></h4>
                                <div class="date"><label><?php echo Labels::getLabel('LBL_Date', $adminLangId); ?> :</label><?php echo FatDate::Format($rfqData['rfq_added_on']); ?></div>
                            </div>
                            <div class=" space">
                                <div class="">
                                    <div class="td__data-right cart-item_wrap mb-3">
                                        <div class="cart-item">
                                            <div class="cart-item__pic">
                                                <a href="<?php echo CommonHelper::generateUrl('Products', 'View', array($rfqData['selprod_id']), CONF_WEBROOT_FRONTEND); ?>">
                                                    <?php $uploadedTime = AttachedFile::setTimeParam($rfqData['product_updated_on']); ?>
                                                    <img data-ratio="1:1 (500x500)" src="<?php echo FatCache::getCachedUrl(CommonHelper::generateUrl('image', 'product', array($rfqData['selprod_product_id'], "CLAYOUT3", $rfqData['selprod_id'], 0, $adminLangId), CONF_WEBROOT_FRONTEND) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg'); ?>" alt="<?php echo $rfqData['selprod_title']; ?>"> 
                                                </a>
                                            </div>
                                            <div class="cart-item__details">
                                                <span class="cart-item__title"><a href="<?php echo CommonHelper::generateUrl('Products', 'View', array($rfqData['selprod_id']), CONF_WEBROOT_FRONTEND); ?>"><?php echo $rfqData['selprod_title']; ?></a>  </span>
                                                <span class="text--normal text--normal-secondary">
                                                    <?php
                                                    if ($rfqData['rfq_request_type'] == applicationConstants::ORDER_TYPE_RENT) {
                                                        echo ($rfqData['rent_in_stock']) ? Labels::getLabel('LBL_In_Stock', $adminLangId) : Labels::getLabel('LBL_Out_Of_Stock', $adminLangId);
                                                    } else {
                                                        echo ($rfqData['in_stock']) ? Labels::getLabel('LBL_In_Stock', $adminLangId) : Labels::getLabel('LBL_Out_Of_Stock', $adminLangId);
                                                    }
                                                    ?>
                                                </span>
                                                <h5>
                                                <?php if ($rfqData['rfq_request_type'] == applicationConstants::ORDER_TYPE_RENT) { 
                                                    echo CommonHelper::displayMoneyFormat($rfqData['sprodata_rental_price'], true, true); 
                                                } else { 
                                                    echo CommonHelper::displayMoneyFormat($rfqData['selprod_price'], true, true); 
                                                }?>    
                                                </h5>
                                                <?php if (!empty($selProdOptions)) { ?>
                                                    <ul class="list--devider">
                                                        <?php
                                                        foreach ($selProdOptions as $option) {
                                                            echo '<li><span>' . $option["option_name"] . ':</span> ' . $option["optionvalue_name"] . '</li>';
                                                        }
                                                        ?>
                                                    </ul>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                    <table class="table table--two-cols no-border-row table-simple">
                                        <tbody>
                                            <tr>
                                                <th><?php echo Labels::getLabel('LBL_Product_Qty', $adminLangId); ?> </th>
                                                <td><?php echo $rfqData['rfq_quantity']; ?></td>
                                            </tr>
                                            <tr>
                                                <th><?php echo Labels::getLabel('LBL_FULFILLMENT_TYPE', $adminLangId); ?></th>
                                                <?php $arr = Shipping::getFulFillmentArr($adminLangId); ?>
                                                <td><?php echo (!empty($arr[$rfqData['rfq_fulfilment_type']])) ?  $arr[$rfqData['rfq_fulfilment_type']] : ''; ?></td>
                                            </tr>
                                            <tr>
                                                <th><?php echo Labels::getLabel('LBL_Order_Type', $adminLangId); ?>
                                                </th>
                                                <?php $OrderType = applicationConstants::getOrderTypeArr($adminLangId); ?>
                                                <td><?php echo (!empty($OrderType[$rfqData['rfq_request_type']])) ?  $OrderType[$rfqData['rfq_request_type']] : ''; ?></td>
                                            </tr>
                                            <?php
                                            if($rfqData['rfq_request_type'] == applicationConstants::ORDER_TYPE_RENT){ ?>
                                            <tr>
                                                <th><?php echo Labels::getLabel('LBL_Rent_Start_Date', $adminLangId); ?></th>
                                                <td><?php echo FatDate::Format($rfqData['rfq_from_date'], true); ?></td>
                                            </tr>
                                            <tr>
                                                <th><?php echo Labels::getLabel('LBL_Rent_End_Date', $adminLangId); ?></th>
                                                <td><?php echo FatDate::Format($rfqData['rfq_to_date'], true); ?></td>
                                            </tr>
                                            <tr>
                                                <th><?php echo Labels::getLabel('LBL_Rental_Duration', $adminLangId); ?></th>
                                                <td><?php echo CommonHelper::getDifferenceBetweenDates($rfqData['rfq_from_date'], $rfqData['rfq_to_date'], 0,  $rfqData['sprodata_duration_type']); ?>
                                                <?php echo $rentalDurArr[$rfqData['sprodata_duration_type']];?>    
                                                </td>
                                            </tr>
                                            
                                            <?php
                                            } ?>
                                            
                                            <tr>
                                                <th><?php echo Labels::getLabel('LBL_Comments_for_Seller', $adminLangId); ?></th>
                                                <td><?php echo $rfqData['rfq_comments']; ?></td>
                                            </tr>
                                            <?php if (!empty($attachments)) { ?>
                                                <tr>
                                                    <th><?php echo Labels::getLabel('LBL_Uploaded_documents', $adminLangId); ?></th>
                                                    <td class="uploaded--documents quoted--offer">   
                                                        <?php
                                                        foreach ($attachments as $attachment) {
                                                            /* $icon = '<i class="fa fa-download"></i>';
                                                              $link = CommonHelper::generateUrl('RequestForQuotes', 'downloadDigitalFile', array($attachment["afile_record_id"], $attachment["afile_id"], AttachedFile::FILETYPE_SERVICE_DOCUMENTS_FOR_SELLER));
                                                              echo '<a target="_blank" href="' . $link . '"><span>' . $attachment["afile_name"] . $icon . '</span></a>'; */
                                                            $attachmentId = $attachment['afile_id'];
                                                            $ext = pathinfo($attachment['afile_name'], PATHINFO_EXTENSION);
                                                            $documentUrl = CommonHelper::generateUrl('RequestForQuotes', 'downloadDigitalFile', [$attachment["afile_record_id"], $attachment["afile_id"], AttachedFile::FILETYPE_SERVICE_DOCUMENTS_FOR_SELLER, true, 70, 70]);
                                                            echo "<span id='document-js-" . $attachmentId . "'>";
                                                            if (in_array($ext, $imageType)) {
                                                                ?>
                                                                <a href="<?php echo CommonHelper::generateUrl('RequestForQuotes', 'downloadDigitalFile', [$attachment["afile_record_id"], $attachmentId, AttachedFile::FILETYPE_SERVICE_DOCUMENTS_FOR_SELLER]); ?>" title="<?php echo Labels::getLabel('LBL_Download_file', $adminLangId); ?>">
                                                                    <img src="<?php echo $documentUrl; ?>" alt="<?php echo $attachment['afile_name']; ?>" title="<?php echo $attachment['afile_name']; ?>" />
                                                                </a>
                                                            <?php } else { ?>
                                                                <a href="<?php echo CommonHelper::generateUrl('RequestForQuotes', 'downloadDigitalFile', [$attachment["afile_record_id"], $attachmentId, AttachedFile::FILETYPE_SERVICE_DOCUMENTS_FOR_SELLER]); ?>" title="<?php echo Labels::getLabel('LBL_Download_file', $adminLangId); ?>">
                                                                    <i class="icn rfq-doc-file-icon">
                                                                        <svg class="svg">
                                                                        <use xlink:href="<?php echo CONF_WEBROOT_FRONT_URL; ?>images/admin/retina/sprite.svg#dash-my-subscriptions" href="<?php echo CONF_WEBROOT_FRONT_URL; ?>images/admin/retina/sprite.svg#dash-my-subscriptions"></use>
                                                                        </svg>
                                                                    </i>
                                                                </a>
                                                                <?php
                                                            }
                                                            echo '<p class="doc-title"><span>' . $icon = $attachment["afile_name"] . '</span></p>';
                                                            echo "</span>";
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </section>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6">
                        <section class="section">
                            <div class="sectionhead">
                                <h4><?php echo Labels::getLabel('LBL_Quoted_Offer_By_Seller', $adminLangId); ?></h4>
                            </div>
                            <div class="space">
                                <div class="">
                                    <?php if (!empty($quotedOfferDetail)) { ?>
                                        <table class="table table--two-cols no-border-row table-simple">
                                            <tbody>
                                                <tr>
                                                    <th><?php echo Labels::getLabel('LBL_Product_Total_Cost', $adminLangId); ?> </th>
                                                    <td><?php echo CommonHelper::displayMoneyFormat($quotedOfferDetail['counter_offer_total_cost']); ?></td>
                                                </tr>
                                                <?php if (!empty($rfqData['rfq_fulfilment_type'] == Shipping::FULFILMENT_SHIP)) { ?>
                                                <tr>
                                                    <th><?php echo Labels::getLabel('LBL_Shipping_Cost', $adminLangId); ?></th>
                                                    <td><?php echo CommonHelper::displayMoneyFormat($quotedOfferDetail['counter_offer_shipping_cost']); ?></td>
                                                </tr>
                                                <?php } ?>
                                                <?php
                                                if($rfqData['rfq_request_type'] == applicationConstants::ORDER_TYPE_RENT){ ?>
                                                <tr>
                                                    <th><?php echo Labels::getLabel('LBL_Rent_Start_Date', $adminLangId); ?></th>
                                                    <td><?php echo FatDate::Format($quotedOfferDetail['counter_offer_from_date'], true); ?></td>
                                                </tr>
                                                <tr>
                                                <th><?php echo Labels::getLabel('LBL_Rent_End_Date', $adminLangId); ?></th>
                                                    <td><?php echo FatDate::Format($quotedOfferDetail['counter_offer_to_date'], true); ?></td>
                                                </tr>
                                                <tr>
                                                    <th><?php echo Labels::getLabel('LBL_Rent_Security_Amount', $adminLangId); ?></th>
                                                    <td><?php echo CommonHelper::displayMoneyFormat($quotedOfferDetail['counter_offer_rental_security']); ?></td>
                                                </tr>
                                                <tr>
                                                    <th><?php echo Labels::getLabel('LBL_Rental_Duration', $adminLangId); ?></th>
                                                    <td><?php echo CommonHelper::getDifferenceBetweenDates($quotedOfferDetail['counter_offer_from_date'], $quotedOfferDetail['counter_offer_to_date'], 0,  $rfqData['sprodata_duration_type']); ?>
                                                        <?php echo $rentalDurArr[$rfqData['sprodata_duration_type']];?>    
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th><?php echo Labels::getLabel('LBL_Quote_Valid_Days', $adminLangId); ?></th>
                                                    <td><?php echo $rfqData['rfq_quote_validity']; ?></td>
                                                </tr>
                                                
                                                <?php
                                                }
                                                ?>
                                                <tr>
                                                    <th><?php echo Labels::getLabel('LBL_Comments_for_Buyer', $adminLangId); ?></th>
                                                    <td><?php echo $quotedOfferDetail['counter_offer_comment']; ?></td>
                                                </tr>
                                                <tr>
                                                    <th><?php echo Labels::getLabel('LBL_Uploaded_documents', $adminLangId); ?></th>
                                                    <td id="uploaded-documents-js" class="uploaded--documents quoted--offer">
                                                        <?php
                                                        if (!empty($quotedAttachments)) {
                                                            foreach ($quotedAttachments as $quotedAttachment) {
                                                                /* $icon = '<i class="fa fa-download"></i>';
                                                                  $link = CommonHelper::generateUrl('RequestForQuotes', 'downloadDigitalFile', array($quotedAttachment["afile_record_id"], $quotedAttachment["afile_id"], AttachedFile::FILETYPE_QUOTED_DOCUMENT));
                                                                  echo '<a target="_blank" href="' . $link . '"><span>' . $quotedAttachment["afile_name"] . $icon . '</span></a>'; */

                                                                $attachmentId = $quotedAttachment['afile_id'];
                                                                $ext = pathinfo($quotedAttachment['afile_name'], PATHINFO_EXTENSION);
                                                                $documentUrl = CommonHelper::generateUrl('RequestForQuotes', 'downloadDigitalFile', [$quotedAttachment["afile_record_id"], $quotedAttachment["afile_id"], AttachedFile::FILETYPE_QUOTED_DOCUMENT, true, 70, 70]);
                                                                echo "<span id='document-js-" . $attachmentId . "'>";
                                                                if (in_array($ext, $imageType)) {
                                                                    ?>
                                                                    <a href="<?php echo CommonHelper::generateUrl('RequestForQuotes', 'downloadDigitalFile', [$quotedAttachment["afile_record_id"], $attachmentId, AttachedFile::FILETYPE_QUOTED_DOCUMENT]); ?>" title="<?php echo Labels::getLabel('LBL_Download_file', $adminLangId); ?>">
                                                                        <img src="<?php echo $documentUrl; ?>" alt="<?php echo $quotedAttachment['afile_name']; ?>" title="<?php echo $quotedAttachment['afile_name']; ?>" />
                                                                    </a>
                                                                <?php } else { ?>
                                                                    <a href="<?php echo CommonHelper::generateUrl('RequestForQuotes', 'downloadDigitalFile', [$quotedAttachment["afile_record_id"], $attachmentId, AttachedFile::FILETYPE_QUOTED_DOCUMENT]); ?>" title="<?php echo Labels::getLabel('LBL_Download_file', $adminLangId); ?>">
                                                                        <i class="icn rfq-doc-file-icon">
                                                                            <svg class="svg">
                                                                            <use xlink:href="<?php echo CONF_WEBROOT_FRONT_URL; ?>images/admin/retina/sprite.svg#dash-my-subscriptions" href="<?php echo CONF_WEBROOT_FRONT_URL; ?>images/admin/retina/sprite.svg#dash-my-subscriptions"></use>
                                                                            </svg>
                                                                        </i>
                                                                    </a>
                                                                    <?php
                                                                }
                                                                echo '<p class="doc-title"><span>' . $icon = $quotedAttachment["afile_name"] . '</span></p>';
                                                                echo "</span>";
                                                            }
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    <?php } else { ?>
                                        <div class="info--order"><p>
                                                <?php echo Labels::getLabel('LBL_Offer_not_quoted_yet_by_seller', $adminLangId); ?>
                                            </p></div>
                                    <?php } ?>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                <div class="row row--cols-group">
                <?php $colVal = (empty($rfqData['pickupAddress'])) ? 12 : 6; ?>
                
                <div class="col-lg-<?php echo $colVal;?> col-md-<?php echo $colVal;?> col-sm-<?php echo $colVal;?>">
                    <section class="section">
                        <div class="sectionhead">
                            <h4><?php echo Labels::getLabel('LBL_Billing_/_Shipping_Details', $adminLangId); ?>
                            </h4>
                        </div>
                        <div class="row space">
                            <?php if (!empty($rfqData['billingAddress'])) { ?>
                            <div class="col-lg-6 col-md-6 col-sm-12">
                                <h5><?php echo Labels::getLabel('LBL_Billing_Details', $adminLangId); ?>
                                </h5>
                                <p><strong><?php echo $rfqData['billingAddress']['addr_name']; ?></strong><br>
                                    <?php
                                    $billingAddress = '';
                                    if ($rfqData['billingAddress']['addr_address1'] != '') {
                                        $billingAddress .= $rfqData['billingAddress']['addr_address1'] . '<br>';
                                    }

                                    if ($rfqData['billingAddress']['addr_address2'] != '') {
                                        $billingAddress .= $rfqData['billingAddress']['addr_address2'] . '<br>';
                                    }

                                    if ($rfqData['billingAddress']['addr_city'] != '') {
                                        $billingAddress .= $rfqData['billingAddress']['addr_city'] . ',';
                                    }

                                    if ($rfqData['billingAddress']['state_name'] != '') {
                                        $billingAddress .= ' ' . $rfqData['billingAddress']['state_name'];
                                    }

                                    if ($rfqData['billingAddress']['addr_zip'] != '') {
                                        $billingAddress .= '-' . $rfqData['billingAddress']['addr_zip'];
                                    }

                                    if ($rfqData['billingAddress']['addr_phone'] != '') {
                                        $billingAddress .= '<br>Phone: ' . $rfqData['billingAddress']['addr_phone'];
                                    }
                                    echo $billingAddress;
                                    ?>
                                </p>
                            </div>
                            <?php 
                            }

                            if (!empty($rfqData['shippingAddress'])) { ?>
                                <div class="col-lg-6 col-md-6 col-sm-12">
                                    <h5><?php echo Labels::getLabel('LBL_Shipping_Details', $adminLangId); ?>
                                    </h5>
                                    <p><strong><?php echo $rfqData['shippingAddress']['addr_name']; ?></strong><br>
                                        <?php
                                        $shippingAddress = '';
                                        if ($rfqData['shippingAddress']['addr_address1'] != '') {
                                            $shippingAddress .= $rfqData['shippingAddress']['addr_address1'] . '<br>';
                                        }

                                        if ($rfqData['shippingAddress']['addr_address2'] != '') {
                                            $shippingAddress .= $rfqData['shippingAddress']['addr_address2'] . '<br>';
                                        }

                                        if ($rfqData['shippingAddress']['addr_city'] != '') {
                                            $shippingAddress .= $rfqData['shippingAddress']['addr_city'] . ',';
                                        }

                                        if ($rfqData['shippingAddress']['state_name'] != '') {
                                            $shippingAddress .= ' ' . $rfqData['shippingAddress']['state_name'];
                                        }

                                        if ($rfqData['shippingAddress']['addr_zip'] != '') {
                                            $shippingAddress .= '-' . $rfqData['shippingAddress']['addr_zip'];
                                        }

                                        if ($rfqData['shippingAddress']['addr_phone'] != '') {
                                            $shippingAddress .= '<br>Phone: ' . $rfqData['shippingAddress']['addr_phone'];
                                        }

                                        echo $shippingAddress;
                                        ?>

                                    </p>
                                </div>
                            <?php } ?>
                        </div>
                    </section>
                </div>

                <?php
                if(isset($rfqData['pickupAddress']) && !empty($rfqData['pickupAddress'])) {
                ?>
                <div class="col-lg-6 col-md-6 col-sm-6">
                    <section class="section">
                        <div class="sectionhead">
                            <h4><?php echo Labels::getLabel('LBL_Pickup_Details', $adminLangId); ?>
                            </h4>
                        </div>
                        <div class="row space">
                        
                            <div class="col-lg-6 col-md-6 col-sm-12">
                                <h5><?php echo Labels::getLabel('LBL_Pickup_Details', $adminLangId); ?>
                                </h5>
                                <p><strong><?php echo $rfqData['pickupAddress']['addr_name']; ?></strong><br>
                                    <?php
                                    $pickupAddress = '';
                                    if ($rfqData['pickupAddress']['addr_address1'] != '') {
                                        $pickupAddress .= $rfqData['pickupAddress']['addr_address1'] . '<br>';
                                    }

                                    if ($rfqData['pickupAddress']['addr_address2'] != '') {
                                        $pickupAddress .= $rfqData['pickupAddress']['addr_address2'] . '<br>';
                                    }

                                    if ($rfqData['pickupAddress']['addr_city'] != '') {
                                        $pickupAddress .= $rfqData['pickupAddress']['addr_city'] . ',';
                                    }

                                    if ($rfqData['pickupAddress']['state_name'] != '') {
                                        $pickupAddress .= ' ' . $rfqData['pickupAddress']['state_name'];
                                    }

                                    if ($rfqData['pickupAddress']['addr_zip'] != '') {
                                        $pickupAddress .= '-' . $rfqData['pickupAddress']['addr_zip'];
                                    }

                                    if ($rfqData['pickupAddress']['addr_phone'] != '') {
                                        $pickupAddress .= '<br>Phone: ' . $rfqData['pickupAddress']['addr_phone'];
                                    }
                                    echo $pickupAddress;
                                    ?>
                                </p>
                            </div>
                        </div>
                    </section>
                </div>
                <?php
                } ?>
            </div>

                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Offers_listing', $adminLangId); ?></h4>
                    </div>
                    <div class="sectionbody">
                        <div id="listing"></div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
<script>
    (function () {
        offersListing = function ($rfqId) {
            var data = 'rfq_id=' + $rfqId;
            $('#listing').html(fcom.getLoader());
            fcom.ajax(fcom.makeUrl('RequestForQuotes', 'offersListing'), data, function (res) {
                $('#listing').html(res);
            });
        };

    })();

    var rfq_id = '<?php echo $rfqData['rfq_id']; ?>';
    offersListing(rfq_id);
</script>

<style>
    .no-border-row td, .no-border-row th {
        border: 0px;
    }

    .cart-item {
        width: 100%;
        display: inline-block;
    }
    .cart-item .cart-item__pic {
        float: left;
        width: 100px;
        margin: 0 0 0 0;
    }
    .cart-item .cart-item__details {
        text-align: left;
        width: calc(100% - 100px);
        padding-left: 15px;
        float: left;
    }

    .cart-item .cart-item__title {
        display: block;
        font-size: 1.2em;
        font-weight: 600;
    }

    .cart-item .text--normal {
        font-size: 0.9em;
    }
    .text--normal-secondary {
        color: #f15c5c !important;
    }

    .quoted--offer span {
        margin-top: 0;
    }
    .uploaded--documents span {
        padding: 2px 6px;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        margin: 0 6px 0 0;
        display: inline-block;
    }

    .uploaded--documents span i {
        margin-left: 10px;
        cursor: pointer;
    }
</style>
