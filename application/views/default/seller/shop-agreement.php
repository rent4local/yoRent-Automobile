<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frmShopAgreement->setFormTagAttribute('class', 'form my-5');
$frmShopAgreement->setFormTagAttribute('onsubmit', 'setupShopAgreement(this); return(false);');

$keyFld = $frmShopAgreement->getField('shop_agreemnet');
$keyFld->addFieldTagAttribute('onchange', 'setupShopAgreementDoc()'); ?>

<?php $variables = array('language' => $language, 'siteLangId' => $siteLangId, 'shop_id' => $shop_id, 'action' => $action);
$this->includeTemplate('seller/_partial/shop-navigation.php', $variables, false); ?>
<div class="tabs__content tabs__content-js">
    <div class="card">
        <div class="card-body">
            <div class="row" id="shopFormBlock">
                <div class="col-md-12">
                    <div class="bg-pattern">
                        <div id="mediaResponse"></div>
                        <div class="row justify-content-center">
                            <div class="col-md-6">
                                <!-- @todo -->
                                <div class="agreements mt-5">
                                    <h6 class="text-center mb-4"><?php echo Labels::getLabel('LBL_Shop_Rental_Agreement', $siteLangId); ?></h6>
                                    <div class="agreements_box">
                                        <?php echo Labels::getLabel('LBL_This_section_will_enable_the_seller_to_add_generalized_rental_agreement_for_their_shop._This_agreement_will_be_displayed_at_the_checkout_for_the_customers_to_review_and_accept._Rental_agreements_will_be_governed_by_this_agreement.', $siteLangId); ?>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <?php if(empty($attachments)) {
                                        if ($canEdit) { 
                                            echo $frmShopAgreement->getFormTag(); ?>
                                            <div class="field-set">
                                                <div class="caption-wraper"><label class="field_label"><?php echo Labels::getLabel('LBL_Upload', $siteLangId); ?></label></div>
                                                <div class="field-wraper">
                                                    <div class="field_cover">
                                                        <?php echo $frmShopAgreement->getFieldHtml('shop_agreemnet'); ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php echo $frmShopAgreement->getFieldHtml('shop_id');
                                        }
                                    } else {
                                        foreach ($attachments as $attachment) {
                                            $attachmentId = $attachment['afile_id'];
                                            $ext = pathinfo($attachment['afile_name'], PATHINFO_EXTENSION);
                                            $documentUrl = UrlHelper::generateUrl('Seller', 'downloadDigitalFile', [$attachment["afile_record_id"], $attachment["afile_id"], AttachedFile::FILETYPE_SHOP_AGREEMENT, true, 70, 70]);
                                            echo "<span id='document-js-" . $attachmentId . "'>"; ?>
                                            <div class="uploaded-files my-5">
                                                <span class="file-name">
                                                    <i class="icn fas fa-file-pdf"></i>
                                                    <a href="<?php echo UrlHelper::generateUrl('Seller', 'downloadDigitalFile', [$attachment["afile_record_id"], $attachmentId, AttachedFile::FILETYPE_SHOP_AGREEMENT]); ?>" title="<?php echo Labels::getLabel('LBL_Download_file', $siteLangId); ?>" download>
                                                        <b class="doc-title"><span><?php echo $icon = $attachment["afile_name"]; ?></span></b>
                                                    </a>
                                                </span>
                                                <?php if ($canEdit) { ?>
                                                <a class="delete" href="javascript:void(0);"
                                                    onClick="deleteShopAgreement(<?php echo $attachmentId; ?>)">
                                                    <svg class="svg" width="16px" height="16px">
                                                        <use
                                                            xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#remove">
                                                        </use>
                                                    </svg>
                                                </a>
                                                <?php } ?>
                                            </div>
                                            <?php echo "</span>";
                                        }
                                    } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>