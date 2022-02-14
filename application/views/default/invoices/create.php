<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');

$frm->setFormTagAttribute('class', 'form form--horizontal layout--ltr');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;
$frm->setFormTagAttribute('onsubmit', 'generateInvoice(this); return(false);');

$btnFld = $frm->getField('btn_submit');
$btnFld->setFieldTagAttribute('class', 'btn btn-brand');

$this->includeTemplate('_partial/seller/sellerDashboardNavigation.php'); ?>
<main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
    <div class="content-header row">
            <div class="col">
                 <h5 class="content-header-title"><?php echo Labels::getLabel('LBL_Invoice_For:', $siteLangId); ?> #<?php echo $orderId; ?></h5>
            </div>
            <div class="col-auto">
            <a href="<?php echo CommonHelper::generateUrl('seller', 'sales'); ?>" class="btn btn-outline-brand btn-sm" title="<?php echo Labels::getLabel('LBL_Back_to_orders', $siteLangId); ?>">
                                    <?php echo Labels::getLabel('LBL_Back_To_Orders', $siteLangId); ?>
                                </a>
            </div>
    </div>
        <div class="content-body">
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="row mb-4">
                        <div class="col-lg-6 col-md-6 col-sm-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title"><?php echo Labels::getLabel('LBL_Buyer_Info', $siteLangId); ?></h5>
                                </div>
                                    <div class="card-body">
                                        <table class="table table-specification">
                                            <tbody><tr>
                                                    <th><?php echo Labels::getLabel('LBL_Name', $siteLangId); ?>: </th>
                                                    <td><?php echo $orderDetails['buyer_name']; ?></td>
                                                </tr>
                                                <tr>
                                                <?php
                                                if(Shipping::FULFILMENT_PICKUP != $orderDetails['rfq_fulfilment_type']){
                                                    $addr = Labels::getLabel('LBL_Delivery_Address', $siteLangId);
                                                }else{
                                                    $addr = Labels::getLabel('LBL_Pickup_Address', $siteLangId);
                                                }
                                                ?>
                                                    <th><?php echo $addr; ?>: </th>
                                                    <td>
                                                        <?php
                                                        $deliveryAddress = '';
                                                        $deliveryAddress .= $addresses['addr_address1'] . '<br>';
                                                        $deliveryAddress .= $addresses['addr_address2'] . '<br>';
                                                        $deliveryAddress .= $addresses['addr_city'] . ', ';
                                                        $deliveryAddress .= $addresses['state_name'] . '<br>';
                                                        $deliveryAddress .= $addresses['country_name'] . ', ' . $addresses['addr_zip'];
                                                        echo $deliveryAddress;
                                                        ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th><?php echo Labels::getLabel('LBL_Contact_No.', $siteLangId); ?>: </th>
                                                    <td><?php echo $addresses['addr_phone']; ?></td>
                                                </tr>
                                                <tr>
                                                    <th><?php echo Labels::getLabel('LBL_Order_Type', $siteLangId); ?>: </th>
                                                    <td><?php 
                                                        $orderArr = applicationConstants::getOrderTypeArr($siteLangId);
                                                        echo $orderArr[$orderDetails['rfq_request_type']]; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th><?php echo Labels::getLabel('LBL_FULFILLMENT_TYPE', $siteLangId); ?>: </th>
                                                    <td><?php 
                                                        $fulfilmetArr = Shipping::getFulFillmentArr($siteLangId);
                                                        echo $fulfilmetArr[$orderDetails['rfq_fulfilment_type']]; ?></td>
                                                </tr>
                                                <?php if ($orderDetails['rfq_request_type'] == applicationConstants::PRODUCT_FOR_RENT) { ?>
                                                <tr>    
                                                    <th><?php echo Labels::getLabel('LBL_Rent_Start_Date', $siteLangId); ?></th>
                                                    <td><?php echo FatDate::Format($orderDetails['opd_rental_start_date'], true); ?></td>
                                                </tr>
                                                <tr>    
                                                    <th><?php echo Labels::getLabel('LBL_Rent_End_Date', $siteLangId); ?></th>
                                                    <td><?php echo FatDate::Format($orderDetails['opd_rental_end_date'], true); ?></td>
                                                </tr>
                                                <?php } ?>    
                                            </tbody>
                                        </table>
                                    </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title"><?php echo Labels::getLabel('LBL_Product_Info', $siteLangId); ?></h5>
                                    <div class="date"><small><label><?php echo Labels::getLabel('LBL_Date', $siteLangId); ?> :</label><?php echo FatDate::Format($orderDetails['rfq_added_on']); ?></small></div>
                                </div>
                                <div class="card-body">
                                    <div class="td__data-right cart-item_wrap"">
                                        <div class="cart-item">
                                            
                                            <div class="cart-item__pic">
                                                <a href="<?php echo CommonHelper::generateUrl('Products', 'View', array($orderDetails['selprod_id']), CONF_WEBROOT_FRONTEND); ?>">
                                                    <?php $uploadedTime = AttachedFile::setTimeParam($orderDetails['product_updated_on']); ?>
                                                    <img data-ratio="1:1 (500x500)" src="<?php echo FatCache::getCachedUrl(CommonHelper::generateUrl('image', 'product', array($orderDetails['selprod_product_id'], "CLAYOUT3", $orderDetails['rfq_selprod_id'], 0, $siteLangId), CONF_WEBROOT_FRONTEND) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg'); ?>" alt="<?php echo $orderDetails['selprod_title']; ?>"> 
                                                </a>
                                            </div>
                                            <div class="cart-item__details">
                                                <span class="cart-item__title"><a href="<?php echo CommonHelper::generateUrl('Products', 'View', array($orderDetails['selprod_id']), CONF_WEBROOT_FRONTEND); ?>"><?php echo $orderDetails['selprod_title']; ?></a>  </span>
                                                <span class="badge badge-pill badge-success">
                                                    <?php
                                                    if ($orderDetails['in_stock']) {
                                                        echo Labels::getLabel('LBL_In_Stock', $siteLangId);
                                                    } else {
                                                        echo Labels::getLabel('LBL_Out_Of_Stock', $siteLangId);
                                                    }
                                                    ?>
                                                </span>
                                                <h5 class="cart-item__price"><?php echo CommonHelper::displayMoneyFormat($orderDetails['selprod_price']); ?></h5>
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
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-6">
                            <div class="card">
                                <div class="card-body">
                                <table class="table table-specification">
                                     <tr>
                                        <th><?php echo Labels::getLabel('LBL_Product_Total_Cost', $siteLangId); ?></th> 
                                       <td> <?php echo CommonHelper::displayMoneyFormat($orderDetails['order_net_amount'] - $orderDetails['op_actual_shipping_charges'] - $orderDetails['order_tax_charged'], true, true, true, false, true); ?></td>
                                    </tr>

                                    <tr>
                                        <th><?php echo Labels::getLabel('LBL_Tax_Charges:', $siteLangId); ?></th>  
                                        <td> <?php echo CommonHelper::displayMoneyFormat($orderDetails['order_tax_charged'], true, true, true, false, true); ?></td>
                                    </tr>

                                    <?php if ($orderDetails['rfq_fulfilment_type'] == Shipping::FULFILMENT_SHIP) { ?>
                                    <tr>
                                        <th><?php echo Labels::getLabel('LBL_Shipping_Charges:', $siteLangId); ?></th>
                                        <td>  <?php echo CommonHelper::displayMoneyFormat($orderDetails['op_actual_shipping_charges'], true, true, true, false, true); ?></td>
                                    </tr>
                                    <?php } ?>
                                    <tr>
                                        <th><?php echo Labels::getLabel('LBL_Order_Total_Amount:', $siteLangId); ?></th>
                                        <td>  <?php echo CommonHelper::displayMoneyFormat($orderDetails['order_net_amount'], true, true, true, false, true); ?></td>
                                    </tr>

                                    <tr>
                                        <th><?php echo Labels::getLabel('LBL_Balance_Amount:', $siteLangId); ?></th>
                                        <td> <?php echo CommonHelper::displayMoneyFormat($orderDetails['order_net_amount'] - $orderDetails['total_paid_amount'], true, true, true, false, true); ?></td>
                                    </tr>
                                
                            </table>
                                </div>
                            </div>
                            
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-6">
                            <div class="card">
                                <div class="card-body">
                                    <?php echo $frm->getFormHtml(); ?> 
                                </div>
                            </div>   
                    </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</main>

<style>
    .table tr td {
        padding: 20px !important;
    }
</style>
<script>
    $(document).ready(function () {
        $('.delivery-date-picker--js').datepicker({
            minDate: new Date(),
            <?php if ($orderDetails['rfq_request_type'] == applicationConstants::PRODUCT_FOR_RENT) { ?>
            maxDate : '<?php echo date('Y-m-d', strtotime($orderDetails['opd_rental_start_date']))?>',
            <?php } ?>
            dateFormat: 'yy-mm-dd',
        });
    });
</script>