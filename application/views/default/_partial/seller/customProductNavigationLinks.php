<div class="card-header">
    <h5 class="card-title"><?php echo Labels::getLabel('LBL_Add_My_Product', $siteLangId); ?></h5>
    <?php if (isset($alertToShow) && $alertToShow) { ?>
    <div class="note-messages"><?php echo Labels::getLabel('LBL_Category_and_brand_fields_are_mandatory', $siteLangId); ?></div>
    <?php }?>
    <div class="action">
        <a href="<?php echo UrlHelper::generateUrl('seller', 'catalog');?>" class="btn btn-brand btn-sm"><strong><?php echo Labels::getLabel('LBL_Back_To_Products_list', $siteLangId)?></strong> </a>
    </div>
</div>
