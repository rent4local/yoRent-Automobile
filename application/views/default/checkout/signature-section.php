<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="step_head">
    <h5 class="step_title"> <?php echo Labels::getLabel('LBL_Signature', $siteLangId); ?></h5>
    <?php if (!empty($signatureData)) { ?>
    <div class="text-right">
        <a class="link" href="javascript:void(0);" onClick="addSign()">
            <span><?php echo Labels::getLabel('LBL_Change', $siteLangId); ?></span>
        </a>                    
    </div>
    <?php } ?>
</div>
<div class="step_body">
    <div class="signature-block">
    <?php if (!empty($signatureData)) { 
        $signatureAdded = 1; 
        $imgUrl = CommonHelper::generateUrl('image', 'signature', array($signatureData['afile_record_id'], 0, 'THUMB', $signatureData['afile_id'], true), CONF_WEBROOT_FRONT_URL).'?t='.  time();
        ?>
        <img src="<?php echo  $imgUrl;?>" title="<?php echo $signatureData['afile_name']; ?>" alt="<?php echo $signatureData['afile_name']; ?>" />
    <?php } else { ?>
        <div class="">
            <p><?php echo Labels::getLabel('LBL_Rental_Signature_text_on_checkout_page', $siteLangId); ?></p>
        </div>
        <div class="">
            <a class="link" href="javascript:void(0);" onClick="addSign()"><span><?php echo Labels::getLabel('LBL_Add', $siteLangId); ?></span></a>
        </div>
    <?php } ?>
    </div>
</div>