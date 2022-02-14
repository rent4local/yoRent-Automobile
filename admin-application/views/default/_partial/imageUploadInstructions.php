<div class="info-text">
    <div class="info-text__icn">
        <i class="fas fa-info-circle"></i>
    </div>
    <div class="info-text__content">
        <ul>
            <li> - <?php echo Labels::getLabel('LBL_Required_Maximum_Image_Size_is_' . AttachedFile::IMAGE_MAX_SIZE_IN_BYTES / 1024 . '_KB', $adminLangId); ?> </li>
            <li> - <?php echo Labels::getLabel('LBL_Supported_extensions_are_webp/jpg/jpeg/png', $adminLangId); ?></li>
            <li> - <?php echo Labels::getLabel('LBL_Recommended_extension_is_Webp_to_improve_the_site_performance', $adminLangId); ?> </li>
        </ul>
    </div>
</div>