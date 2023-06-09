<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$verificationForm->setFormTagAttribute('id', 'frmSubmitVerificationFlds');
$verificationForm->setFormTagAttribute('class', 'form');
$verificationForm->setFormTagAttribute('onsubmit', 'submitVerificationFlds(this); return(false);');
$verificationForm->developerTags['colClassPrefix'] = 'col-md-';
$verificationForm->developerTags['fld_default_col'] = 6;
$submitBtnFld = $verificationForm->getField('btn_submit');
$submitBtnFld->setFieldTagAttribute('class', 'btn btn-brand btn-wide');

$signatureEnable = 0;
$verficationFlsEnable = 0;
$signatureAdded = 1;
$is_Sign_added = 1;
?>
    <div class="step_section">
        <div class="step_body">
            <ul class="review-block">
                <li>
                    <div class="review-block__label">
                        <?php
                        if ($fulfillmentType == Shipping::FULFILMENT_PICKUP || $cartHasPhysicalProduct == false) {
                            echo Labels::getLabel('LBL_Billing_to:', $siteLangId);
                            $address = $billingAddressArr;
                        } else {
                            echo Labels::getLabel('LBL_Shipping_to:', $siteLangId);
                            $address = $shippingAddressArr;
                        }
                        ?>
                    </div>

                    <div class="review-block__content">
                        <div class="delivery-address">
                            <p><?php echo $address['addr_name'] . ', ' . $address['addr_address1']; ?>
                                <?php
                                if (strlen($address['addr_address2']) > 0) {
                                    echo ", " . $address['addr_address2'];
                                    ?>
                                <?php } ?>
                            </p>
                            <p><?php echo $address['addr_city'] . ", " . $address['state_name'] . ", " . $address['country_name'] . ", " . $address['addr_zip']; ?>
                            </p>
                            <?php if (strlen($address['addr_phone']) > 0) { ?>
                                <p class="phone-txt"><i class="fas fa-mobile-alt"></i> <?php echo $address['addr_dial_code'] . ' ' . $address['addr_phone']; ?>
                                </p>
                            <?php } ?>
                        </div>
                    </div>

                </li>

                <?php if ($fulfillmentType == Shipping::FULFILMENT_PICKUP && !empty($orderPickUpData)) { ?>
                    <li>
                        <div class="review-block__label">
                            <?php echo Labels::getLabel('LBL_Pickup_Address', $siteLangId); ?>
                        </div>
                        <div class="review-block__content">
                            <div class="delivery-address">
                                <?php foreach ($orderPickUpData as $address) { ?>
                                    <p><strong><?php echo ($address['opshipping_by_seller_user_id'] > 0) ? $address['op_shop_name'] : FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId, null, ''); ?></strong>
                                    </p>
                                    <p><?php echo $address['oua_name'] . ', ' . $address['oua_address1']; ?>
                                        <?php
                                        if (strlen($address['oua_address2']) > 0) {
                                            echo ", " . $address['oua_address2'];
                                            ?>
                                        <?php } ?>
                                    </p>
                                    <p><?php echo $address['oua_city'] . ", " . $address['oua_state'] . ", " . $address['oua_country'] . ", " . $address['oua_zip']; ?></p>
                                    <?php if (strlen($address['oua_phone']) > 0) { ?>
                                        <p class="phone-txt"><i class="fas fa-mobile-alt"></i> <?php echo $address['oua_dial_code'] . ' ' . $address['oua_phone']; ?></p>
                                    <?php } ?>

                                    <?php
                                    $fromTime = isset($address["opshipping_time_slot_from"]) && !empty($address["opshipping_time_slot_from"]) ? date('H:i', strtotime($address["opshipping_time_slot_from"])) : '';
                                    $toTime = isset($address["opshipping_time_slot_to"]) && !empty($address["opshipping_time_slot_to"]) ? date('H:i', strtotime($address["opshipping_time_slot_to"])) : '';
                                    ?>
                                    <p class="time-txt">
                                        <i class="fas fa-calendar-day"></i>
                                        <?php
                                        $opshippingDate = isset($address["opshipping_date"]) ? FatDate::format($address["opshipping_date"]) : '';
                                        echo $opshippingDate . ' ' . $fromTime . ' - ' . $toTime;
                                        ?>
                                    </p>
                                <?php } ?>
                            </div>
                        </div>
                    </li>
                <?php } ?>

                <?php if ($cartHasPhysicalProduct && $fulfillmentType == Shipping::FULFILMENT_SHIP && !empty($orderShippingData)) { ?>
                    <li>
                        <div class="review-block__label">
                            <?php echo Labels::getLabel('LBL_Shipping:', $siteLangId); ?>
                        </div>
                        <div class="review-block__content">
                            <div class="shipping-data">
                                <ul class="media-more media-more-sm show">
                                    <?php foreach ($orderShippingData as $shipData) { ?>
                                        <?php
                                        foreach ($shipData as $data) {
                                            if ($data['opd_product_type'] == SellerProduct::PRODUCT_TYPE_ADDON) {
                                                $imgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'addonProduct', array($data['op_selprod_id'], "THUMB", 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
                                            } else {
                                                $imgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($data['selprod_product_id'], "THUMB", $data['op_selprod_id'], 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
                                            }
                                            ?>
                                            <li>
                                                <span class="circle" data-toggle="tooltip" data-placement="top"
                                                      title="<?php echo $data['op_selprod_title']; ?>"
                                                      data-original-title="<?php echo $data['op_selprod_title']; ?>">
                                                    <img src="<?php echo $imgUrl; ?>"
                                                         alt="<?php echo $data['op_selprod_title']; ?>">
                                                </span>
                                            </li>
                                        <?php } ?>
                                        <?php
                                        break;
                                    }
                                    ?>
                                </ul>
                                <div class="shipping-data_title"><?php echo $data['opshipping_label']; ?></div>
                            </div>
                        </div>
                    </li>
                <?php } ?>

                <?php if ($cartHasPhysicalProduct && $fulfillmentType == Shipping::FULFILMENT_SHIP && $shippingAddressId != $billingAddressId) {
                    ?>
                    <li>
                        <div class="review-block__label">
                            <?php echo Labels::getLabel('LBL_Billing_to:', $siteLangId); ?>
                        </div>
                        <div class="review-block__content">
                            <p><?php echo $billingAddressArr['addr_name'] . ', ' . $billingAddressArr['addr_address1']; ?>
                                <?php
                                if (strlen($billingAddressArr['addr_address2']) > 0) {
                                    echo ", " . $billingAddressArr['addr_address2'];
                                    ?>
                                <?php } ?>
                            </p>
                            <p><?php echo $billingAddressArr['addr_city'] . ", " . $billingAddressArr['state_name']; ?></p>
                            <p><?php echo $billingAddressArr['country_name'] . ", " . $billingAddressArr['addr_zip']; ?></p>
                            <?php if (strlen($billingAddressArr['addr_phone']) > 0) { ?>
                                <p class="phone-txt"><i
                                        class="fas fa-mobile-alt"></i> <?php echo $billingAddressArr['addr_dial_code'] . ' ' . $billingAddressArr['addr_phone']; ?></p>
                                <?php } ?>
                        </div>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <?php
    echo $verificationForm->getFormTag();
    if (!empty($vfldSortedData)) {
        $verficationFlsEnable = 1;
        ?>
        <div class="step_section">
            <div class="step_head">
                <h5 class="step_title"><?php echo Labels::getLabel('LBL_Additional_documents_required_for_this_order', $siteLangId); ?></h5>
            </div>
            <div class="step_body">
                <div class="verified-box form">
                    <ul class="verified-box-list">
                        <?php 
                        foreach ($vfldSortedData as $key => $val) {
                            $flds_arr = explode("_", $key);
                            ?>
                            <li>
                                <ul class="media-more media-more-sm show">
                                    <?php
                                    $i = 0;
                                    foreach ($val as $pKey => $pVal) {
                                        if ($i == 5) {
                                            echo "<li> <span class='circle plus-more'>+ 1</span></li>";
                                            break;
                                        }
                                        
                                        
                                        $imageUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($cartProductData['selprod_product_id'], "THUMB", $cartProductData['selprod_id'], 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
                                        ?>
                                        <li>
                                            <span class="circle" data-toggle="tooltip" data-placement="top" title=""
                                                  data-original-title="<?php echo $cartProductData['selprod_title']; ?>">
                                                <img src="<?php echo $imageUrl; ?>" alt="<?php echo $cartProductData['selprod_title']; ?>">
                                            </span>
                                        </li>
                                        <?php
                                        $i++;
                                    }
                                    ?>
                                </ul>
                                <?php
                                foreach ($flds_arr as $fldKey) {
                                    $fldVal = str_split($fldKey);
                                    $fldkeyStart = $fldVal[0];
                                    unset($fldVal[0]);
                                    $fldkeyEnd = implode('', $fldVal);
                                    
                                    $fldName = ($fldkeyStart == 'f') ? 'fileFld_' . $fldkeyEnd . '' : 'textFld_' . $fldkeyEnd . '';
                                    $fld = $verificationForm->getField($fldName);
                                    ?>
                                    <div class="row form-group ">
                                        <div class="col-md-4">
                                            <label class="field_label"><?php echo $fld->getCaption(); ?><?php if ($fld->requirements()->isRequired()) { ?><span class="spn_must_field">*</span><?php } ?></label>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="custom-upload err_<?php echo $fldName; ?>">
                                                <?php echo $verificationForm->getFieldHTML($fldName); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }

    if (!empty($attachmentArr) && FatApp::getConfig("CONF_SHOP_AGREEMENT_AND_SIGNATURE", FatUtility::VAR_INT, 1)) {
        ?>
        <div class="step_section">
            <div class="step_head">
                <h5 class="step_title"><?php echo Labels::getLabel('LBL_Rental_Agreement', $siteLangId); ?></h5>
            </div>
            <div class="step_body">
                <div class="attached-files scroll scroll-x">
                    <ul class="">
                        <li class="">
                            <h6><?php echo $cartProductData['op_shop_name']; ?></h6>
                            <a href="<?php echo UrlHelper::generateUrl('RfqCheckout', 'downloadDigitalFile', [$attachmentArr["afile_record_id"], $attachmentArr['afile_id'], AttachedFile::FILETYPE_SHOP_AGREEMENT]); ?>"
                               title="<?php echo Labels::getLabel('LBL_Download_file', $siteLangId); ?>">
                                <i class="icn fas fa-file-pdf"></i>
                                <?php echo $icon = $attachmentArr["afile_name"]; ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
        $signatureEnable = 1;
        $signatureAdded = (!empty($signatureData)) ? 1 : 0;
        ?>
        <div class="step_section" id="e_sign">
            <div class="step_head">
                <h5 class="step_title"><?php echo Labels::getLabel('LBL_Signature', $siteLangId); ?></h5>
                <a class="link" href="javascript:void(0);" onClick="addSign()"><span><?php echo Labels::getLabel('LBL_Change', $siteLangId); ?></span></a>
            </div>

            <div class="step_body">
                <div class="signature-block">
                    <?php
                    $is_Sign_added = 0;
                    if (!empty($signatureData)) {
                        $is_Sign_added = 1;
                        ?>
                        <img src="<?php echo CommonHelper::generateUrl('image', 'signature', array($signatureData['afile_record_id'], 0, 'THUMB', $signatureData['afile_id'], true), CONF_WEBROOT_FRONT_URL); ?>?t=<?php echo time(); ?>" title="<?php echo $signatureData['afile_name']; ?>" alt="<?php echo $signatureData['afile_name']; ?>">
                    <?php } else { ?>
                        <div class="">
                            <p><?php echo Labels::getLabel('LBL_Rental_Signature_text_on_checkout_page', $siteLangId); ?></p>
                        </div>
                        <div class="">
                            <a class="link" href="javascript:void(0);" onClick="addSign()"><span><?php echo Labels::getLabel('LBL_Add', $siteLangId); ?></span></a>
                        </div>
                    <?php } ?>
                    <input type="hidden" name="is_Sign_added" value="<?php echo $is_Sign_added; ?>" />
                    <input type="hidden" name="agreement" value="<?php echo FatApp::getConfig("CONF_SHOP_AGREEMENT_AND_SIGNATURE", FatUtility::VAR_INT, 1); ?>" />
                </div>
            </div>
        </div>
        <label class="checkbox" for="accept_term">
            <input type="checkbox" name="accept_term" value="1"> 
            <?php echo Labels::getLabel('LBL_I_have_read_&_accept_the_rental_terms_mentioned_in_the_agreements', $siteLangId); ?>
        </label>
    <?php } ?>

    <div class="step_foot">
        <?php if ($cartHasPhysicalProduct) { ?>
            <a class="btn btn-outline-brand btn-wide" href="javascript:void(0)" onclick="changeShipping();"><?php echo Labels::getLabel('LBL_Back', $siteLangId); ?></a>
        <?php } else { ?>
            <a class="btn btn-outline-brand btn-wide" href="javascript:void(0)" onclick="showAddressList();"><?php echo Labels::getLabel('LBL_Back', $siteLangId); ?></a>
        <?php } ?>
        <?php
        echo $verificationForm->getFieldHTML('orderId');
        echo $verificationForm->getFieldHTML('orderNumericId');
        echo $verificationForm->getFieldHTML('btn_submit');
        ?>
    </div>
    <?php echo $verificationForm->getExternalJS(); ?>
    <script type="text/javascript">
        var signatureEnable = <?php echo $signatureEnable; ?>;
        var verficationFlsEnable = <?php echo $verficationFlsEnable; ?>;
        var signatureAdded = <?php echo $signatureAdded; ?>;
        $("document").ready(function () {
<?php if (FatApp::getConfig("CONF_SHOP_AGREEMENT_AND_SIGNATURE", FatUtility::VAR_INT, 1) && !$is_Sign_added && !empty($attachmentArr)) { ?>
                addSign();
<?php } ?>
        });
    </script>

     <script>
        $(document).ready(function () {
            $('input[type=file]').change(function () {
                var obj = $(this);
                var fileName = this.files[0].name;
                var id = $(this).attr('id');
                var fileTxt =  ' <span class="file-name">'+ fileName +'</span><a class="delete" href="javascript:void(0);" data-id="'+ id +'" onClick="deleteTempFile(this);"><svg class="svg" width="16px" height="16px"><use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#remove" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#remove"></use></svg></a>';
                
                $($('label[for="'+ id +'"]')).hide();  
                $('.' + id).html(fileTxt);
                $('.' + id).show();
            });
            
            deleteTempFile = function(obj) {
                var id = $(obj).data('id');
                document.getElementById(id).value = "";
                $($('label[for="'+ id +'"]')).show();
                $('.' + id).html(" ");
                $('.' + id).hide();
            };
        });
    </script>