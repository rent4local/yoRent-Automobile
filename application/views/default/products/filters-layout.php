<sidebar class="collection-sidebar" id="collection-sidebar" data-close-on-click-outside="collection-sidebar">

    <?php if (array_key_exists('brand_id', $postedData) && $postedData['brand_id'] > 0) {
                    ?>
    <div class="shop-information">
        <div class="shop-logo">
            <?php
                        $fileData = AttachedFile::getAttachment(AttachedFile::FILETYPE_BRAND_LOGO, $postedData['brand_id'], 0, 0, false);
                        $aspectRatioArr = AttachedFile::getRatioTypeArray($siteLangId);
                        ?>
            <img <?php if ($fileData['afile_aspect_ratio'] > 0) { ?>
                data-ratio="<?php echo $aspectRatioArr[$fileData['afile_aspect_ratio']]; ?>" <?php } ?>
                src="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'brand', array($postedData['brand_id'] , $siteLangId, 'COLLECTION_PAGE')), CONF_IMG_CACHE_TIME, '.jpg'); ?>"
                alt="<?php echo (!empty($fileData['afile_attribute_alt'])) ? $fileData['afile_attribute_alt'] : $pageTitle;?>"
                title="<?php echo (!empty($fileData['afile_attribute_alt'])) ? $fileData['afile_attribute_alt'] : $pageTitle;?>">
        </div>
    </div> <?php
                } ?>
    <div class="filters productFilters-js">

    </div>
</sidebar>