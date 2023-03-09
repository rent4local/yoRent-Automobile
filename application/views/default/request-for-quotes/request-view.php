<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php $this->includeTemplate('_partial/buyerDashboardNavigation.php'); 
    
$rentalDurArr = ProductRental::durationTypeArr($siteLangId); ?>
<main id="main-area" class="main offer-detail-page" role="main">
    <div class="content-wrapper content-space">
        <div class="content-header row">
            <div class="col">
            <span>(#<?php echo $rfqData['rfq_id']; ?>)</span>
            <span class="badge badge-warning badge-pill"><?php echo $statusArr[$rfqData['rfq_status']]; ?></span>
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Details', $siteLangId); ?>: <?php echo $rfqData['selprod_title']; ?> </h2>
            </div>
            <div class="col-auto">
                <div class="btn-group rfq-view-action-btn">
                    <?php if ($rfqData['rfq_parent_id'] > 0) { ?>
                        <a href="<?php echo UrlHelper::generateUrl('RequestForQuotes', 'RequestView', array($rfqData['rfq_parent_id'])); ?>" class="btn btn-outline-brand btn-sm"><?php echo Labels::getLabel('LBL_Back_Original_Offer', $siteLangId); ?></a>
                    <?php } ?>
                    
                    <?php if ($rfqData['order_id'] != '' && $rfqData['order_payment_status'] == Orders::ORDER_PAYMENT_PENDING && strtotime($rfqData['rfq_quote_validity']) >= strtotime(date('Y-m-d')) && $rfqData['rfq_status'] == RequestForQuote::REQUEST_APPROVED && $rfqData['invoice_status'] == Invoice::INVOICE_IS_SHARED_WITH_BUYER) { ?>
                        <a href="<?php echo UrlHelper::generateUrl('RfqCheckout', 'index', array($rfqData['order_id'])); ?>" class="btn btn-outline-brand btn-sm"><?php echo Labels::getLabel('LBL_Pay_Now', $siteLangId); ?></a>
                    <?php } ?>
                    
                    <a href="<?php echo UrlHelper::generateUrl('RequestForQuotes', $action); ?>" class="btn btn-outline-brand btn-sm"><?php echo Labels::getLabel('LBL_Back_to_request_listing', $siteLangId); ?></a>

                    <a href="javascript:void(0)" onClick="rfqMeaasge(<?php echo $rfqData['rfq_id'];?>)" class="btn btn-outline-brand btn-sm"><?php echo Labels::getLabel('LBL_Send_Messages', $siteLangId); ?></a>
                </div>
            </div>

        </div>
        <!--  <div class="row">
            <div class="col-lg-12">
                
            </div>
        </div>  -->

        <div class="content-body">
            <div class="row mb-4">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title"><?php echo Labels::getLabel('LBL_Original_Offer', $siteLangId); ?></h5>
                            <small>
                                <?php echo Labels::getLabel('LBL_Date', $siteLangId); ?> : <?php echo FatDate::Format($rfqData['rfq_added_on']); ?>
                            </small>
                        </div>
                        <div class="card-body">
                        <div class="td__data-right cart-item_wrap">
                                <div class="cart-item">
                                    <div class="cart-item__pic">
                                <a href="<?php echo UrlHelper::generateUrl('Products', 'View', array($rfqData['selprod_id'])); ?>">
                                    <?php $uploadedTime = AttachedFile::setTimeParam($rfqData['product_updated_on']); ?>
                                    <?php if ($rfqData['selprod_type'] == SellerProduct::PRODUCT_TYPE_ADDON) { ?>
                                        <img data-ratio="1:1" src="<?php echo FatCache::getCachedUrl(CommonHelper::generateUrl('image', 'serviceProduct', array($rfqData['selprod_id'], "SMALL", 0, $siteLangId), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg') ?>" alt="<?php echo $rfqData['selprod_title']; ?>">
                                    <?php } else { ?>
                                        <img data-ratio="1:1" src="<?php echo FatCache::getCachedUrl(CommonHelper::generateUrl('image', 'product', array($rfqData['selprod_product_id'], "CLAYOUT3", $rfqData['selprod_id'], 0, $siteLangId)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg'); ?>" alt="<?php echo $rfqData['selprod_title']; ?>">
                                    <?php } ?>
                                </a>
                                    </div>

                            <div class="cart-item__details">
                                        <span class="cart-item__title"><a href="<?php echo UrlHelper::generateUrl('Products', 'View', array($rfqData['selprod_id'])); ?>"><?php echo $rfqData['selprod_title']; ?></a> </span>
                                        <span class="badge badge-pill badge-success">
                                            <?php
                                            if ($rfqData['rfq_request_type'] == applicationConstants::ORDER_TYPE_RENT) {
                                                echo ($rfqData['rent_in_stock']) ? Labels::getLabel('LBL_In_Stock', $siteLangId) : Labels::getLabel('LBL_Out_Of_Stock', $siteLangId);
                                            } else {
                                                echo ($rfqData['in_stock']) ? Labels::getLabel('LBL_In_Stock', $siteLangId) : Labels::getLabel('LBL_Out_Of_Stock', $siteLangId);
                                            }
                                            ?>
                                        </span>
                                        <h5 class="cart-item__price">
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

                            <div class="box-group__body box-group__body-js">
                                <div class=""></div>
                                <table class="table table-specification">
                                    <tbody>
                                        <tr>
                                            <th><?php echo Labels::getLabel('LBL_Product_Qty', $siteLangId); ?> </th>
                                            <td><?php echo $rfqData['rfq_quantity']; ?></td>
                                        </tr>
                                        <tr>
                                            <th><?php echo Labels::getLabel('LBL_FULFILLMENT_TYPE', $siteLangId); ?>
                                            </th>
                                            <?php $arr = Shipping::getFulFillmentArr($siteLangId); ?>
                                            <td><?php echo (!empty($arr[$rfqData['rfq_fulfilment_type']])) ?  $arr[$rfqData['rfq_fulfilment_type']] : ''; ?></td>
                                        </tr>
                                        <tr>
                                            <?php if (!empty($rfqData['rfq_fulfilment_type'] == Shipping::FULFILMENT_SHIP)) { ?>
                                                <th><?php echo Labels::getLabel('LBL_Delivery_Address', $siteLangId); ?>: </th>
                                            <?php } else { ?>
                                                <th><?php echo Labels::getLabel('LBL_Pickup_Address', $siteLangId); ?>:</th>
                                            <?php } ?>
                                            <td><?php
                                                $deliveryAddress = '';
                                                if(!empty($shippingAddressDetail)) {
                                                    $deliveryAddress .= $shippingAddressDetail['addr_address1'] . '<br>';
                                                    $deliveryAddress .= $shippingAddressDetail['addr_address2'] . '<br>';
                                                    $deliveryAddress .= $shippingAddressDetail['addr_city'] . ', ';
                                                    $deliveryAddress .= $shippingAddressDetail['state_name'] . '<br>';
                                                    $deliveryAddress .= $shippingAddressDetail['country_name'] . ', ' . $shippingAddressDetail['addr_zip'];
                                                }
                                                echo $deliveryAddress;
                                            ?></td>
                                        </tr>
                                        <tr>
                                            <th><?php echo Labels::getLabel('LBL_Order_Type', $siteLangId); ?>
                                            </th>
                                            <?php $OrderType = applicationConstants::getOrderTypeArr($siteLangId); ?>
                                            <td><?php echo (!empty($OrderType[$rfqData['rfq_request_type']])) ?  $OrderType[$rfqData['rfq_request_type']] : ''; ?></td>
                                        </tr>
                                        <?php
                                        if($rfqData['rfq_request_type'] == applicationConstants::ORDER_TYPE_RENT){ ?>
                                        <tr>
                                            <th><?php echo Labels::getLabel('LBL_Rent_Start_Date', $siteLangId); ?></th>
                                            <td><?php echo FatDate::Format($rfqData['rfq_from_date'], true); ?></td>
                                        </tr>
                                        <tr>
                                            <th><?php echo Labels::getLabel('LBL_Rent_End_Date', $siteLangId); ?></th>
                                            <td><?php echo FatDate::Format($rfqData['rfq_to_date'], true); ?></td>
                                        </tr>
                                        <tr>
                                            <th><?php echo Labels::getLabel('LBL_Rental_Duration', $siteLangId); ?></th>
                                            <td><?php echo CommonHelper::getDifferenceBetweenDates($rfqData['rfq_from_date'], $rfqData['rfq_to_date'], 0,  $rfqData['sprodata_duration_type']); ?>
                                            <?php echo $rentalDurArr[$rfqData['sprodata_duration_type']];?>    
                                            </td>
                                        </tr>
                                        
                                        
                                        <?php
                                        } ?>
                                        
                                        <tr>
                                            <th><?php echo Labels::getLabel('LBL_Comments_for_Seller', $siteLangId); ?></th>
                                            <td><?php echo $rfqData['rfq_comments']; ?></td>
                                        </tr>
                                        <?php if (!empty($attachments)) { ?>
                                            <tr>
                                                <th><?php echo Labels::getLabel('LBL_Uploaded_documents', $siteLangId); ?></th>
                                                <td class="uploaded--documents quoted--offer">
                                                    <?php
                                                    $imageType = array('png', 'jpeg', 'jpg', 'gif', 'bmp', 'ico', 'tiff', 'tif');
                                                    foreach ($attachments as $attachment) {
                                                        $attachmentId = $attachment['afile_id'];
                                                        $ext = pathinfo($attachment['afile_name'], PATHINFO_EXTENSION);
                                                        $documentUrl = UrlHelper::generateUrl('CounterOffer', 'downloadDigitalFile', [$attachment["afile_record_id"], $attachment["afile_id"], AttachedFile::FILETYPE_SERVICE_DOCUMENTS_FOR_SELLER, true, 70, 70]);
                                                        echo "<div class='uploaded--documents-item' id='document-js-" . $attachmentId . "'>";
                                                        if (in_array($ext, $imageType)) {
                                                    ?>
                                                            <a href="<?php echo UrlHelper::generateUrl('CounterOffer', 'downloadDigitalFile', [$attachment["afile_record_id"], $attachmentId, AttachedFile::FILETYPE_SERVICE_DOCUMENTS_FOR_SELLER]); ?>" title="<?php echo Labels::getLabel('LBL_Download_file', $siteLangId); ?>">
                                                                <img src="<?php echo $documentUrl; ?>" alt="<?php echo $attachment['afile_name']; ?>" title="<?php echo $attachment['afile_name']; ?>" />
                                                            </a>
                                                        <?php } else { ?>
                                                            <a href="<?php echo UrlHelper::generateUrl('CounterOffer', 'downloadDigitalFile', [$attachment["afile_record_id"], $attachmentId, AttachedFile::FILETYPE_SERVICE_DOCUMENTS_FOR_SELLER]); ?>" title="<?php echo Labels::getLabel('LBL_Download_file', $siteLangId); ?>">
                                                                <i class="icn rfq-doc-file-icon">
                                                                    <svg class="svg">
                                                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-my-subscriptions" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#dash-my-subscriptions"></use>
                                                                    </svg>
                                                                </i>
                                                            </a>
                                                    <?php
                                                        }
                                                        echo '<p class="doc-title"><span>' . $icon = $attachment["afile_name"] . '</span></p>';
                                                        echo "</div>";

                                                         $icon = '<i class="fa fa-download"></i>';
                                                              $link = UrlHelper::generateUrl('CounterOffer', 'downloadDigitalFile', array($attachment["afile_record_id"], $attachment["afile_id"], AttachedFile::FILETYPE_SERVICE_DOCUMENTS_FOR_SELLER));
                                                              echo '<a target="_blank" href="' . $link . '"><span>' . $attachment["afile_name"] . $icon . '</span></a>';
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title"><?php echo Labels::getLabel('LBL_Quoted_Offer_By_Seller', $siteLangId); ?> 
                           </h5>
                           <small><?php echo Labels::getLabel('LBL_All_Prices_in', $siteLangId); ?> [<?php echo CommonHelper::getSystemDefaultCurrenyCode(); ?>]</small>
                        </div>

                        <div class="card-body">
                                    <?php if (!empty($quotedOfferDetail)) { ?>
									<?php if (!empty($servicesList)) { ?>
										<h6 class="text-danger"><?php echo Labels::getLabel('LBL_All_Prices_are_including_services_prices', $siteLangId); ?></h6>
									<?php } ?>

                                        <table class="table table-specification">
                                            <tbody>
                                                <tr>
                                                    <th><?php echo Labels::getLabel('LBL_Product_Total_Cost', $siteLangId); ?> </th>
                                                    <td><?php echo CommonHelper::displayMoneyFormat($quotedOfferDetail['counter_offer_total_cost'], true, true); ?><br /> <small class="">(<?php echo Labels::getLabel('LBL_Excluded_Shipping_and_Tax_Charges', $siteLangId); ?>)</small></td>
                                                </tr>
												<?php if (!empty($rfqData['rfq_fulfilment_type'] == Shipping::FULFILMENT_SHIP)) { ?>
                                                <tr>
                                                    <th><?php echo Labels::getLabel('LBL_Shipping_Cost', $siteLangId); ?></th>
                                                    <td><?php echo CommonHelper::displayMoneyFormat($quotedOfferDetail['counter_offer_shipping_cost'], true, true); ?></td>
                                                </tr>
                                                <?php } ?>
                                                <tr>
                                                    <th><?php echo Labels::getLabel('LBL_Quote_Valid_till', $siteLangId); ?> </th>
                                                    <td><?php echo FatDate::Format($quotedOfferDetail['rfq_quote_validity']); ?></td>
                                                </tr>
                                                <?php
                                                if($rfqData['rfq_request_type'] == applicationConstants::ORDER_TYPE_RENT){ ?>
                                                <tr>
                                                    <th><?php echo Labels::getLabel('LBL_Rental_Security_Amount', $siteLangId); ?>
                                                    </th>
                                                    <td><?php echo CommonHelper::displayMoneyFormat($quotedOfferDetail['counter_offer_rental_security'], true, true); ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th><?php echo Labels::getLabel('LBL_Rent_Start_Date', $siteLangId); ?></th>
                                                    <td><?php echo FatDate::Format($quotedOfferDetail['counter_offer_from_date'], true); ?></td>
                                                </tr>
                                                <tr>
                                                    <th><?php echo Labels::getLabel('LBL_Rent_End_Date', $siteLangId); ?></th>
                                                    <td><?php echo FatDate::Format($quotedOfferDetail['counter_offer_to_date'], true); ?></td>
                                                </tr>
                                                <tr>
                                                    <th><?php echo Labels::getLabel('LBL_Rental_Duration', $siteLangId); ?></th>
                                                    <td><?php echo CommonHelper::getDifferenceBetweenDates($quotedOfferDetail['counter_offer_from_date'], $quotedOfferDetail['counter_offer_to_date'], 0,  $rfqData['sprodata_duration_type']); ?>
                                                        <?php echo $rentalDurArr[$rfqData['sprodata_duration_type']];?>    
                                                    </td>
                                                </tr>
                                                <?php
                                                } ?>
                                                
                                                <tr>
                                                    <th><?php echo Labels::getLabel('LBL_Comments_for_Buyer', $siteLangId); ?></th>
                                                    <td><?php echo $quotedOfferDetail['counter_offer_comment']; ?></td>
                                                </tr>
                                                <tr>
                                                    <th><?php echo Labels::getLabel('LBL_Uploaded_documents', $siteLangId); ?></th>
                                                    <td id="uploaded-documents-js" class="uploaded--documents quoted--offer"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    <?php } else { ?>
                                        <div class="info--order-form">
                                            <p>
                                                <?php echo Labels::getLabel('LBL_Offer_not_quoted_yet_by_seller', $siteLangId); ?>
                                            </p>
                                        </div>
                                    <?php } ?>
                                
                        </div>
                    </div>
                </div>
            </div>
			<!-- ATTACHED SERVICES LISTING  -->
            <?php if (!empty($servicesList)) { ?>
			<div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title"><?php echo Labels::getLabel('LBL_Attached_Addons', $siteLangId); ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div>
							    <table class="table">
									<thead>
                                            <tr>
                                                <th><?php echo Labels::getLabel('LBL_Addon_Name', $siteLangId); ?> </th>
                                                <th><?php echo Labels::getLabel('LBL_Addon_Original_Price', $siteLangId); ?> </th>
                                                <th><?php echo Labels::getLabel('LBL_Addon_Qty', $siteLangId); ?> </th>
                                                <th><?php echo Labels::getLabel('LBL_Addon_Capacity', $siteLangId); ?></th>
                                            </tr>
									</thead>		

                                            <tbody>
                                                <?php
                                                foreach ($servicesList as $service) {
                                                    $attachedDocs = (isset($attachments[$service['rfqattser_selprod_id']])) ? $attachments[$service['rfqattser_selprod_id']] : [];
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $service['selprod_title']; ?></td>
                                                        <td><?php
                                                            echo CommonHelper::displayMoneyFormat($service['selprod_price'], true, true);
                                                            ;
                                                            ?></td>
                                                        <td><?php echo $service['rfqattser_quantity']; ?></td>
                                                        <td><?php echo $service['rfqattser_required_capacity']; ?></td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    
							</div>
                        </div>
                    </div>
                </div>
            </div>
			<?php } ?>
            <!-- ] -->
			
			
            <div class="row mb-4" id="offers-listing-js">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title"><?php echo Labels::getLabel('LBL_Offers_listing', $siteLangId); ?></h5>
                        </div>
                        <div class="card-body">
                            <div id="listing"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" id="counter-offer-form-section-js" style="display:none">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title"> <?php echo Labels::getLabel('LBL_Counter_Offer', $siteLangId); ?> </h5>
                        </div>
                        <div class="card-body">
                            <div id="counter-offer-form-js"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
    var rfq_id = '<?php echo $rfqData['rfq_id']; ?>';
    offersListing(rfq_id);

    <?php if (!empty($quotedOfferDetail)) { ?>
        getUploadedDocuments(rfq_id);
    <?php } ?>
</script>