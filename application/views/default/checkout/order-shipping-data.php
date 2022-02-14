<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
if(!empty($orderShippingData)){ 
?>
<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">
            <span
                class="primary-color"><?php echo Labels::getLabel('LBL_Shipping', $siteLangId); ?></span></h5>
   
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<div class="modal-body">
<ul class="review-block">
    <?php foreach($orderShippingData as $shipData) { ?>
    <li class="list-group-item">
        <div class="review-block__content">
            <div class="shipping-data">
                <ul class="media-more media-more-sm show">

                    <?php foreach($shipData as $data) { ?>
                    <li>
                        <span class="circle" data-toggle="tooltip" data-placement="top"
                            title="<?php echo $data['op_selprod_title']; ?>"
                            data-original-title="<?php echo $data['op_selprod_title']; ?>">
                            <?php
                                        if ($data['opd_product_type'] == SellerProduct::PRODUCT_TYPE_ADDON) {
                                            $imgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'addonProduct', array($data['op_selprod_id'], "THUMB", 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
                                        } else {
                                            $imgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($data['selprod_product_id'], "THUMB", $data['op_selprod_id'], 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
                                        }
                                        ?>
                            <img src="<?php echo $imgUrl; ?>" alt="<?php echo $data['op_selprod_title']; ?>">
                        </span>
                    </li>
                    <?php } ?>
                    <div class="shipping-data_title"><?php 
                            if ($data['opd_product_type'] == SellerProduct::PRODUCT_TYPE_ADDON) {
                                echo Labels::getLabel('LBL_Rental_Service(No_Shipping_Required)', $siteLangId);
                            } else {
                                echo $data['opshipping_label'];
                            }
                            ?>
                    </div>
                </ul>
            </div>
        </div>
    </li>
    <?php } ?>
</ul>
<div class="d-flex"><button class="btn btn-outline-brand mleft-auto" type="button"
        onClick="ShippingSummaryData();"><?php echo Labels::getLabel('LBL_Edit', $siteLangId); ?></button></div>
<?php } else { ?>
<div class="pop-up-title"><?php echo Labels::getLabel('LBL_No_Pick_Up_address_added', $siteLangId); ?></div>
<?php } ?>

</div>
</div>
</div>

<script>
ShippingSummaryData = function() {
    /* $("#facebox .close").trigger('click'); */
    $("#exampleModal .close").click();
    loadShippingSummaryDiv();
}
</script>