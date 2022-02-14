<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php if (!empty($images)) { ?>
<div class="col-md-12">
    <div class="profile__pic">
        <img src="<?php echo UrlHelper::generateUrl('Image', 'shopCollectionImage', array($images['afile_record_id'], $images['afile_lang_id'], 'THUMB')).'&t='. time();?>" alt="<?php echo Labels::getLabel('LBL_Collection_Image', $siteLangId);?>">
    </div>
    <small class="form-text text-muted"><?php echo $languages[$images['afile_lang_id']];?></small>
    <div class="btngroup--fix">
        <a class = "btn btn-brand btn-sm" href="javascript:void(0);" onClick="removeCollectionImage(<?php echo $scollection_id; ?>,<?php echo $lang_id; ?>)"><?php echo Labels::getLabel('LBL_Remove', $siteLangId);?></a>
    </div>
</div>
<?php } ?>
