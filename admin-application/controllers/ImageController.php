<?php

class ImageController extends FatController
{
    public function default_action()
    {
        exit(Labels::getLabel('LBL_Invalid_Request', CommonHelper::getLangId()));
    }

    public function product($recordId, $size_type = '', $afile_id = 0)
    {
        $default_image = 'product_default_image.jpg';
        $recordId = FatUtility::int($recordId);
        $afile_id = FatUtility::int($afile_id);

        if ($afile_id > 0) {
            $res = AttachedFile::getAttributesById($afile_id);
            if (!false == $res && $res['afile_type'] == AttachedFile::FILETYPE_PRODUCT_IMAGE) {
                $file_row = $res;
            }
        } else {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_PRODUCT_IMAGE, $recordId);
        }
        $image_name = isset($file_row['afile_physical_path']) ? AttachedFile::FILETYPE_PRODUCT_IMAGE_PATH . $file_row['afile_physical_path'] : '';

        switch (strtoupper($size_type)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'SMALL':
                $w = 200;
                $h = 200;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                $h = 400;
                $w = 400;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
        }
    }

    public function siteAdminLogo($lang_id = 0, $sizeType = '')
    {
        $lang_id = FatUtility::int($lang_id);
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_ADMIN_LOGO, 0, 0, $lang_id);
        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
        $default_image = '';
        AttachedFile::displayImage($image_name, 0, 0, $default_image, '', ImageResize::IMG_RESIZE_RESET_DIMENSIONS);
        /* switch( strtoupper($sizeType) ){
        case 'THUMB':
        $w = 142;
        $h = 45;
        AttachedFile::displayImage( $image_name, $w, $h, $default_image );
        break;
        case 'SMALL':
        $w = 200;
        $h = 200;
        AttachedFile::displayImage( $image_name, $w, $h, $default_image );
        break;
        default:
        $h = 400;
        $w = 400;
        AttachedFile::displayImage( $image_name, $w, $h, $default_image );
        break;
        } */
    }

    public function profileImage($adminId, $sizeType = '', $cropedImage = false)
    {
        $default_image = 'user_deafult_image.jpg';
        $recordId = FatUtility::int($adminId);

        if ($cropedImage == true) {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_ADMIN_PROFILE_CROPED_IMAGE, $recordId);
        } else {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_ADMIN_PROFILE_IMAGE, $recordId);
        }

        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'CROPED':
                $w = 230;
                $h = 230;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                AttachedFile::displayOriginalImage($image_name, $default_image);
                break;
        }
    }

    public function brandFeaturedImage($recordId, $langId = 0, $sizeType = '', $afile_id = 0, $slide_screen = 0)
    {
        $default_image = 'brand-default.png';
        $recordId = FatUtility::int($recordId);
        $afile_id = FatUtility::int($afile_id);
        $langId = FatUtility::int($langId);

        if ($afile_id > 0) {
            $res = AttachedFile::getAttributesById($afile_id);
            if (!false == $res && $res['afile_type'] == AttachedFile::FILETYPE_BRAND_FEATURED_IMAGE) {
                $file_row = $res;
            }
        } else {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_BRAND_FEATURED_IMAGE, $recordId, 0, $langId);
        }

        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
        $sizeArr = imagesSizes::brandFeaturedImageSizeArr()[applicationConstants::getActiveTheme()];
        

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 61;
                $h = 61;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'COLLECTION_PAGE':
                AttachedFile::displayOriginalImage($image_name, $default_image);
                break;
            case 'MOBILE':
                $w = $sizeArr['width'] / 2;
                $h = $sizeArr['height'] / 2;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'TABLET':
                $w = $sizeArr['width'] / 2;
                $h = $sizeArr['height'] / 2;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'DESKTOP':
                $w = $sizeArr['width'] / 2;
                $h = $sizeArr['height'] / 2;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                AttachedFile::displayOriginalImage($image_name, $default_image);
                break;
        }
    }

    public function pwaImage($type)
    {
        $type = FatUtility::int($type);
        $file_row = AttachedFile::getAttachment($type,0);
        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
        AttachedFile::displayOriginalImage($image_name, '');
    }
}
