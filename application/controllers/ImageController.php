<?php

class ImageController extends FatController
{

    public function __construct()
    {
        CommonHelper::initCommonVariables();
    }

    public function user($recordId, $sizeType = '', $cropedImage = 0, $afile_id = 0)
    {
        $default_image = 'user_deafult_image.jpg';
        $recordId = FatUtility::int($recordId);
        $afile_id = FatUtility::int($afile_id);
        $cropedImage = FatUtility::int($cropedImage);

        $fileType = ($cropedImage) ? AttachedFile::FILETYPE_USER_PROFILE_CROPED_IMAGE : AttachedFile::FILETYPE_USER_PROFILE_IMAGE;

        if ($afile_id > 0) {
            $res = AttachedFile::getAttributesById($afile_id);
            if (!false == $res && $res['afile_type'] == $fileType) {
                $file_row = $res;
            }
        } else {
            //FILETYPE_USER_IMAGE
            //FILETYPE_f_PROFILE_IMAGE
            $file_row = AttachedFile::getAttachment($fileType, $recordId);
            if ($cropedImage && $file_row == false) {
                $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_USER_PROFILE_IMAGE, $recordId);
            }
        }

        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 150;
                $h = 150;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'MINI':
                $w = 70;
                $h = 70;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'SMALL':
                $w = 200;
                $h = 200;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'MEDIUM':
                $w = 500;
                $h = 500;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                /* $h = 100;
                  $w = 100; */
                AttachedFile::displayOriginalImage($image_name);
                break;
        }
    }

    public function customProduct($recordId, $sizeType, $afile_id = 0, $lang_id = 0)
    {
        $default_image = 'product_default_image.jpg';
        $recordId = FatUtility::int($recordId);
        $afile_id = FatUtility::int($afile_id);
        $lang_id = FatUtility::int($lang_id);

        if ($row) {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_CUSTOM_PRODUCT_IMAGE, $row['afile_record_id'], $row['afile_record_subid'], $lang_id);
        } elseif ($afile_id > 0) {
            $res = AttachedFile::getAttributesById($afile_id);
            if (!false == $res && $res['afile_type'] == AttachedFile::FILETYPE_CUSTOM_PRODUCT_IMAGE) {
                $file_row = $res;
            }
        }

        if ($file_row == false) {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_CUSTOM_PRODUCT_IMAGE, $recordId, 0, $lang_id);
        }
        $image_name = (isset($file_row['afile_physical_path']) && !empty($file_row['afile_physical_path'])) ? AttachedFile::FILETYPE_PRODUCT_IMAGE_PATH . $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'SMALL':
                // image size required in product listing
                $w = 150;
                $h = 150;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'MEDIUM':
                $w = 542;
                $h = 480;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                $h = 400;
                $w = 400;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
        }
    }

    /*
      function product(){}
      ARG1-> $recordId -> required, (product_id) if passed only then will fetch default single main image
      ARG2-> $sizeType -> required, (SMALL, LARGE, THUMB) etc if passed then show image as per requested Size.
      ARG3-> $selprod_id -> selprod_id, optional, if passed, will show option value specific image if uploaded, caluclated by itself,
      ARG4-> $afile_id -> optional, if passed, will fetch direct file, but care, recordId and sizeType needs to passed, and pass selprod_id = 0
     */

    public function product($recordId, $sizeType, $selprod_id = 0, $afile_id = 0, $lang_id = 0)
    {
        $default_image = 'product-default.png';
        $recordId = FatUtility::int($recordId);
        $afile_id = FatUtility::int($afile_id);
        $selprod_id = FatUtility::int($selprod_id);
        $lang_id = FatUtility::int($lang_id);

        /* code to fetch color specific images for a single product, and varies according to option value id, E.g: Color: White, Black, Grey[ */
        if ($selprod_id) {
            $srch = SellerProduct::getSearchObject();
            $srch->doNotCalculateRecords();
            $srch->joinTable(SellerProduct::DB_TBL_SELLER_PROD_OPTIONS, 'INNER JOIN', 'selprod_id = selprodoption_selprod_id', 'tspo');
            $srch->joinTable(OptionValue::DB_TBL, 'INNER JOIN', 'tspo.selprodoption_optionvalue_id = opval.optionvalue_id', 'opval');
            $srch->joinTable(Option::DB_TBL, 'INNER JOIN', 'opval.optionvalue_option_id = op.option_id', 'op');
            $srch->joinTable(AttachedFile::DB_TBL, 'INNER JOIN', 'sp.selprod_product_id = af.afile_record_id AND af.afile_record_subid =  tspo.selprodoption_optionvalue_id', 'af');
            $srch->addCondition('selprod_id', '=', $selprod_id);
            $srch->addCondition('af.afile_type', '=', AttachedFile::FILETYPE_PRODUCT_IMAGE);
            $srch->addOrder('af.afile_display_order');

            /* if( $lang_id > 0 ){ */
            $cnd = $srch->addCondition('af.afile_lang_id', '=', $lang_id);
            $cnd->attachCondition('af.afile_lang_id', '=', 0);
            $srch->addOrder('af.afile_lang_id');
            /* } */

            $srch->addDirectCondition('selprodoption_selprod_id IS NOT NULL', 'AND');
            $srch->addDirectCondition('af.afile_id IS NOT NULL', 'AND');
            $srch->setPageNumber(1);
            $srch->setPageSize(1);
            /* $srch->addMultipleFields(array('selprod_id', 'selprod_product_id', 'selprodoption_option_id', 'afile_id', 'afile_record_id', 'afile_record_subid')); */
            $srch->addMultipleFields(array('afile_id', 'afile_record_id', 'afile_record_subid'));
            $rs = $srch->getResultSet();
            $row = FatApp::getDb()->fetch($rs);
            /* CommonHelper::printArray($row); die(); */
        }
        /* ] */
        $file_row = false;
        if ($selprod_id && $row) {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_PRODUCT_IMAGE, $row['afile_record_id'], $row['afile_record_subid'], $lang_id);
        } elseif ($afile_id > 0) {
            $res = AttachedFile::getAttributesById($afile_id);
            if (!false == $res && $res['afile_type'] == AttachedFile::FILETYPE_PRODUCT_IMAGE) {
                $file_row = $res;
            }
        }

        if ($file_row == false) {
            //echo 'sds'; die("here");
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_PRODUCT_IMAGE, $recordId, -1, $lang_id);
        }

        $image_name = (isset($file_row['afile_physical_path']) && !empty($file_row['afile_physical_path'])) ? AttachedFile::FILETYPE_PRODUCT_IMAGE_PATH . $file_row['afile_physical_path'] : '';
        /* CommonHelper::printArray($image_name); die();  */
        $sizeArr = imagesSizes::productImageSizeArr()[applicationConstants::getActiveTheme()];

        switch (strtoupper($sizeType)) {
            case 'GRID':
                $image_name = str_replace(AttachedFile::FILETYPE_PRODUCT_IMAGE_PATH, AttachedFile::FILETYPE_PRODUCT_IMAGE_PATH_THUMB, $image_name);
                AttachedFile::displayOriginalImage($image_name, $default_image, '', false, true);
                break;
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'MINI':
                $w = 50;
                $h = 50;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, false);
                break;
            case 'EXTRA-SMALL':
                $w = 60;
                $h = 60;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, false);
                break;
            case 'SMALL':
                // image size required in product listing
                $w = 230;
                $h = 230;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
            case 'MEDIUM':
                $w = $sizeArr['width'] / 2; // 500
                $h = $sizeArr['height'] / 2; // 500
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
            case 'CLAYOUT3':
                $w = $sizeArr['width'] / 2; // 230
                $h = $sizeArr['height'] / 2; // 230
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
            case 'CLAYOUT4':
                $w = $sizeArr['width']; // 230
                $h = $sizeArr['height']; // 230
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
            case 'CATLAYOUT3':
                $w = $sizeArr['width'] / 2; // 348
                $h = $sizeArr['height'] / 2; // 438
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
            case 'CLAYOUT2':
                $w = $sizeArr['width'] / 2; // 398
                $h = $sizeArr['height'] / 2; // 398
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
            case 'AUTOCLAYOUT2':
                $w = 280;
                $h = 210;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
            case 'AUTOCLAYOUT3':
                $w = 380;
                $h = 285;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;    
            case 'AUTOCLAYOUT5':
                $w = $sizeArr['width']; 
                $h = $sizeArr['height'];
                /* $w = 588;
                $h = 441; */
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
            case 'ORIGINAL':
                $w = $sizeArr['width']; // 1500
                $h = $sizeArr['height']; // 1500
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
            case 'FB_RECOMMEND':
                $w = 1200;
                $h = 630;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
            case 'PRODUCT_LAYOUT_1':
                $w = $sizeArr['width'] / 2; // 348
                $h = $sizeArr['height'] / 2; // 438
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
            case 'PRODUCT_LAYOUT_4':
                $w = $sizeArr['width'] / 2; // 383
                $h = $sizeArr['height'] / 2; // 486
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
            case 'PRODUCT_LAYOUT_5':
                $w = $sizeArr['width'];
                $h = $sizeArr['height'];
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
            case 'DETAIL':

                $w = $sizeArr['width']; // 438
                $h = $sizeArr['height']; // 584
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
            default:
                $w = $sizeArr['width']; // 400
                $h = $sizeArr['height']; // 400
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
        }
    }

    public function shopLogo($recordId, $lang_id = 0, $sizeType = '', $afile_id = 0, $displayUniversalImage = true)
    {
        $default_image = 'shop-default.png';

        $recordId = FatUtility::int($recordId);
        $afile_id = FatUtility::int($afile_id);
        $lang_id = FatUtility::int($lang_id);

        if ($afile_id > 0) {
            $res = AttachedFile::getAttributesById($afile_id);
            if (!false == $res && $res['afile_type'] == AttachedFile::FILETYPE_SHOP_LOGO) {
                $file_row = $res;
            }
        } else {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_SHOP_LOGO, $recordId, 0, $lang_id, $displayUniversalImage);
        }

        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'THUMB2':
                $w = 168;
                $h = 104;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'MINI':
                $w = 50;
                $h = 50;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, false);
                break;
            case 'EXTRA-SMALL':
                $w = 60;
                $h = 60;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, false);
                break;
            case 'SMALL':
                // image size required in product listing
                $w = 230;
                $h = 230;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
            case 'MEDIUM':
                $w = 500;
                $h = 500;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
            case 'SHOP_LAYOUT_2':
                $w = 400;
                $h = 400;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
            case 'THUMB3':
                $w = 240;
                $h = 180;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;

            case 'ORIGINAL':
                AttachedFile::displayOriginalImage($image_name, $default_image);
                break;
            default:
                AttachedFile::displayOriginalImage($image_name, $default_image);
                break;
        }
    }

    public function promotion_banner($img = '', $type)
    {
        $default_image = 'shop_banner.png';
        switch (strtoupper($type)) {
            case 'MINI':
                return AttachedFile::displayImage($img, 50, 50, 'promotions/', 'shop_default.jpg');
                break;
            default:
                return AttachedFile::displayImage($img, 50, 50, $default_image);
        }
    }

    public function shopBanner($recordId, $lang_id = 0, $sizeType = '', $afile_id = 0, $screen = 0)
    {
        $default_image = 'shop_banner.png';

        $recordId = FatUtility::int($recordId);
        $afile_id = FatUtility::int($afile_id);
        $lang_id = FatUtility::int($lang_id);

        if ($afile_id > 0) {
            $file_row = AttachedFile::getAttributesById($afile_id);
            if (false == $file_row || (!false == $file_row && $file_row['afile_type'] != AttachedFile::FILETYPE_SHOP_BANNER)) {
                return;
            }
        } else {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_SHOP_BANNER, $recordId, 0, $lang_id, true, $screen);
        }

        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'TEMP1':
                $w = 2000;
                $h = 500;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'MOBILE':
                $w = 640;
                $h = 360;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'TABLET':
                $w = 1024;
                $h = 360;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'DESKTOP':
                $w = 2000;
                $h = 500;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                AttachedFile::displayOriginalImage($image_name, $default_image);
                break;
        }
    }

    public function promotionMedia($recordId, $lang_id = 0, $sizeType = '', $afile_id = 0)
    {
        $default_image = 'product_default_image.jpg';

        $recordId = FatUtility::int($recordId);
        $afile_id = FatUtility::int($afile_id);
        $lang_id = FatUtility::int($lang_id);

        if ($afile_id > 0) {
            $file_row = AttachedFile::getAttributesById($afile_id);
            if (false == $file_row || (!false == $file_row && $file_row['afile_type'] != AttachedFile::FILETYPE_PROMOTION_MEDIA)) {
                return;
            }
        } else {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_PROMOTION_MEDIA, $recordId, 0, $lang_id);
        }

        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'TEMP2':
                $w = 1298;
                $h = 600;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'TEMP3':
                $w = 1583;
                $h = 475;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'TEMP4':
                $w = 1583;
                $h = 473;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'TEMP5':
                $w = 1440;
                $h = 600;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                $w = 1298;
                $h = 600;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
        }
    }

    public function shopBackgroundImage($recordId, $lang_id = 0, $sizeType = '', $afile_id = 0, $templateId = '')
    {
        switch ($templateId) {
            case Shop::TEMPLATE_ONE:
                $default_image = 'images/defaults/' . 'logo-red.png';
                break;
            case Shop::TEMPLATE_TWO:
                $default_image = 'images/defaults/' . 'transparent.png';
                break;
            case Shop::TEMPLATE_THREE:
                $default_image = 'images/defaults/' . 'transparent.png';
                break;
            case Shop::TEMPLATE_FOUR:
                $default_image = 'images/defaults/' . 'shop-bg.jpg';
                break;
            case Shop::TEMPLATE_FIVE:
                $default_image = 'images/defaults/' . 'shop-5-bg.jpg';
                break;
            default:
                $h = '';
                $w = '';
                $default_image = '';
                break;
        }


        $recordId = FatUtility::int($recordId);
        $afile_id = FatUtility::int($afile_id);
        $lang_id = FatUtility::int($lang_id);

        if ($afile_id > 0) {
            $file_row = AttachedFile::getAttributesById($afile_id);
            if (false == $file_row || (!false == $file_row && $file_row['afile_type'] != AttachedFile::FILETYPE_SHOP_BACKGROUND_IMAGE)) {
                return;
            }
        } else {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_SHOP_BACKGROUND_IMAGE, $recordId, 0, $lang_id);
        }

        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
        if ($image_name == '' || empty($image_name)) {
            $image_name = $default_image;
        }
        switch (strtoupper($sizeType)) {
            default:
                $h = '';
                $w = '';
                AttachedFile::displayOriginalImage($image_name, $default_image);
                break;
        }
    }

    public function brandReal($recordId, $langId = 0, $sizeType = '', $afile_id = 0)
    {
        $this->displayBrandLogo($recordId, $langId, $sizeType, $afile_id, false);
    }

    public function brand($recordId, $langId = 0, $sizeType = '', $afile_id = 0)
    {
        $this->displayBrandLogo($recordId, $langId, $sizeType, $afile_id);
    }

    public function brandImage($recordId, $langId = 0, $sizeType = '', $afile_id = 0, $slide_screen = 0)
    {
        $this->displayBrandImage($recordId, $langId, $sizeType, $afile_id, $slide_screen);
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
            case 'AUTOLOGO':
                $w = 120;
                $h = 120;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                AttachedFile::displayOriginalImage($image_name, $default_image);
                break;
        }
    }

    public function displayBrandLogo($recordId, $langId = 0, $sizeType = '', $afile_id = 0, $displayUniversalImage = true)
    {
        $default_image = 'brand-default.png';
        $recordId = FatUtility::int($recordId);
        $afile_id = FatUtility::int($afile_id);
        $langId = FatUtility::int($langId);

        if ($afile_id > 0) {
            $res = AttachedFile::getAttributesById($afile_id);
            if (!false == $res && $res['afile_type'] == AttachedFile::FILETYPE_BRAND_LOGO) {
                $file_row = $res;
            }
        } else {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_BRAND_LOGO, $recordId, 0, $langId, $displayUniversalImage);
        }
        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'MINITHUMB':
                $w = 42;
                $h = 52;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'THUMB':
                $w = 61;
                $h = 61;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'COLLECTION_PAGE':
                AttachedFile::displayOriginalImage($image_name, $default_image);
                break;
            case 'LISTING_PAGE':
                $h = 530;
                $w = 530;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'BRAND_LAYOUT_2':
                $h = 85;
                $w = 140;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_RESET_DIMENSIONS);
                break;
            default:
                $h = 500;
                $w = 500;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
        }
    }

    public function displayBrandImage($recordId, $langId = 0, $sizeType = '', $afile_id = 0, $screen = 0, $displayUniversalImage = true)
    {
        $default_image = 'brand-default.png';
        $recordId = FatUtility::int($recordId);
        $afile_id = FatUtility::int($afile_id);
        $langId = FatUtility::int($langId);

        if ($afile_id > 0) {
            $res = AttachedFile::getAttributesById($afile_id);
            if (!false == $res && $res['afile_type'] == AttachedFile::FILETYPE_BRAND_IMAGE) {
                $file_row = $res;
            }
        } else {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_BRAND_IMAGE, $recordId, 0, $langId, $displayUniversalImage, $screen);
        }
        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';

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
                $w = 640;
                $h = 360;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'TABLET':
                $w = 1024;
                $h = 360;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'DESKTOP':
                $w = 2000;
                $h = 500;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'LOGONEW':
                $w = 160;
                $h = 90;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                AttachedFile::displayOriginalImage($image_name, $default_image);
                break;
        }
    }

    /*
     * All Payment methods moved to plugins section.
     */

    public function paymentMethod($recordId, $sizeType = '', $afile_id = 0)
    {
        $default_image = 'product_default_image.jpg';

        $recordId = FatUtility::int($recordId);
        $afile_id = FatUtility::int($afile_id);

        if ($afile_id > 0) {
            $file_row = AttachedFile::getAttributesById($afile_id);
            // if (false == $file_row || (!false == $file_row && $file_row['afile_type'] != AttachedFile::FILETYPE_PAYMENT_METHOD)) {
            if (false == $file_row || (!false == $file_row && $file_row['afile_type'] != AttachedFile::FILETYPE_PLUGIN_LOGO)) {
                return;
            }
        } else {
            // $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_PAYMENT_METHOD, $recordId);
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_PLUGIN_LOGO, $recordId);
        }

        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'ICON':
                $w = 30;
                $h = 30;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'MINITHUMB':
                $w = 61;
                $h = 61;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
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
            case 'MEDIUM':
                $w = 250;
                $h = 250;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                $h = 400;
                $w = 400;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
        }
    }

    public function shopLayout($recordId, $sizeType = '')
    {
        $default_image = 'shop-default.png';

        $recordId = FatUtility::int($recordId);
        $filePath = LayoutTemplate::LAYOUTTYPE_SHOP_IMAGE_PATH;

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 200;
                $h = 200;
                AttachedFile::displayImage($recordId, $w, $h, $default_image, $filePath);
                break;
            case 'SMALL':
                $w = 250;
                $h = 250;
                AttachedFile::displayImage($recordId, $w, $h, $default_image, $filePath);
                break;
            default:
                $h = 400;
                $w = 400;
                AttachedFile::displayImage($recordId, $w, $h, $default_image, $filePath);
                break;
        }
    }

    public function siteLogo($lang_id = 0, $sizeType = '')
    {
        $lang_id = FatUtility::int($lang_id);
        $recordId = 0;
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_FRONT_LOGO, $recordId, 0, $lang_id, false);
        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
        $default_image = '16X9.png';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h, '1X1.png');
                break;
            case '1X1':
                $w = 45;
                $h = 45;
                AttachedFile::displayImage($image_name, $w, $h, '1X1.png');
                break;
            case '16X9':
                $w = 80;
                $h = 45;
                AttachedFile::displayImage($image_name, $w, $h, '16X9.png');
                break;
            case 'CUSTOM':
                AttachedFile::displayOriginalImage($image_name, $default_image);
                break;    
            default:
                $h = 37;
                $w = 168;
                AttachedFile::displayOriginalImage($image_name, $default_image);
                break;
        }
    }

    public function emailLogo($lang_id = 0, $sizeType = '')
    {
        $lang_id = FatUtility::int($lang_id);
        $recordId = 0;
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_EMAIL_LOGO, $recordId, 0, $lang_id);
        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
        $default_image = 'no_image.jpg';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                $w = 100;
                $h = 100;
                if ($image_name == '' || empty($image_name)) {
                    AttachedFile::displayImage($image_name, $w, $h, $default_image);
                } else {
                    /* echo $image_name; die; */
                    AttachedFile::displayOriginalImage($image_name, $default_image);
                }
                break;
        }
    }

    public function socialFeed($lang_id = 0, $sizeType = '')
    {
        $lang_id = FatUtility::int($lang_id);
        $recordId = 0;
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_SOCIAL_FEED_IMAGE, $recordId, 0, $lang_id);
        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
        $default_image = '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 120;
                $h = 80;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                $h = 240;
                $w = 160;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
        }
    }

    public function paymentPageLogo($lang_id = 0, $sizeType = '')
    {
        $lang_id = FatUtility::int($lang_id);
        $recordId = 0;
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_PAYMENT_PAGE_LOGO, $recordId, 0, $lang_id);
        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
        $default_image = '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 168;
                $h = 37;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                $w = 268;
                $h = 82;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
        }
    }

    public function watermarkImage($lang_id = 0, $sizeType = '')
    {
        $lang_id = FatUtility::int($lang_id);
        $recordId = 0;
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_WATERMARK_IMAGE, $recordId, 0, $lang_id);
        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
        $default_image = '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                AttachedFile::displayOriginalImage($image_name, $default_image);
                break;
        }
    }

    public function appleTouchIcon($lang_id = 0, $sizeType = '')
    {
        $lang_id = FatUtility::int($lang_id);
        $recordId = 0;
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_APPLE_TOUCH_ICON, $recordId, 0, $lang_id);
        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
        $default_image = '';

        switch (strtoupper($sizeType)) {
            case 'MINI':
                $w = 72;
                $h = 72;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'SMALL':
                $w = 114;
                $h = 114;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                $arr = explode('-', $sizeType);
                if (count($arr) > 0) {
                    list($w, $h) = $arr;
                    AttachedFile::displayImage($image_name, $w, $h, $default_image);
                } else {
                    AttachedFile::displayOriginalImage($image_name, $default_image);
                }
                break;
        }
    }

    public function mobileLogo($lang_id = 0, $sizeType = '')
    {
        $lang_id = FatUtility::int($lang_id);
        $recordId = 0;
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_MOBILE_LOGO, $recordId, 0, $lang_id);
        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
        $default_image = '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                $h = 82;
                $w = 268;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
        }
    }

    public function invoiceLogo($lang_id = 0, $sizeType = '')
    {
        $lang_id = FatUtility::int($lang_id);
        $recordId = 0;
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_INVOICE_LOGO, $recordId, 0, $lang_id);
        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
        $default_image = '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                $h = 37;
                $w = 168;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
        }
    }

    public function CategoryCollectionBgImage($langId = 0, $sizeType = '')
    {
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_CATEGORY_COLLECTION_BG_IMAGE, $recordId, 0, $langId);
        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h);
                break;
            default:
                AttachedFile::displayOriginalImage($image_name);
                break;
        }
    }

    public function BrandCollectionBgImage($langId = 0, $sizeType = '')
    {
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_BRAND_COLLECTION_BG_IMAGE, $recordId, 0, $langId);
        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h);
                break;
            default:
                AttachedFile::displayOriginalImage($image_name);
                break;
        }
    }

    public function coupon($coupon_id, $lang_id = 0, $sizeType = '')
    {
        $coupon_id = FatUtility::int($coupon_id);

        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_DISCOUNT_COUPON_IMAGE, $coupon_id, 0, $lang_id);
        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
        $default_image = '';
        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'NORMAL':
                $w = 120;
                $h = 120;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                $w = 600;
                $h = 400;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
        }
    }

    public function metaImage($lang_id = 0, $sizeType = '')
    {
        $lang_id = FatUtility::int($lang_id);
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_META_IMAGE, 0, 0, $lang_id);
        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
        $default_image = '';

        switch (strtoupper($sizeType)) {
            default:
                $w = 600;
                $h = 400;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
        }
    }

    public function firstPurchaseCoupon($lang_id = 0, $sizeType = '')
    {
        $lang_id = FatUtility::int($lang_id);
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_FIRST_PURCHASE_DISCOUNT_IMAGE, 0, 0, $lang_id);
        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
        $default_image = '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'NORMAL':
                $w = 120;
                $h = 150;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                $w = 600;
                $h = 400;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
        }
    }

    public function favicon($lang_id = 0, $sizeType = '')
    {
        $lang_id = FatUtility::int($lang_id);
        $recordId = 0;
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_FAVICON, $recordId, 0, $lang_id);
        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
        $default_image = '';

        switch (strtoupper($sizeType)) {
            case 'MINI':
                $w = 72;
                $h = 72;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'SMALL':
                $w = 114;
                $h = 114;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                $arr = explode('-', $sizeType);
                if (count($arr) > 0) {
                    list($w, $h) = $arr;
                    AttachedFile::displayImage($image_name, $w, $h, $default_image);
                } else {
                    AttachedFile::displayOriginalImage($image_name, $default_image);
                }
                break;
        }
    }

    public function slide($slide_id, $screen = 0, $lang_id, $sizeType = '', $displayUniversalImage = true)
    {
        $default_image = 'slider-default.png';
        $slide_id = FatUtility::int($slide_id);
        $activeTheme = applicationConstants::getActiveTheme();
        $dimenssionArr = imagesSizes::heroSlideImageSizeArr();
        $dimenssionArr = (isset($dimenssionArr[$activeTheme])) ? $dimenssionArr[$activeTheme] : $dimenssionArr[applicationConstants::THEME_DEFAULT];

        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_HOME_PAGE_BANNER, $slide_id, 0, $lang_id, $displayUniversalImage, $screen);
        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
        $image_name = AttachedFile::FILETYPE_HOME_PAGE_BANNER_PATH . $image_name;
        if ($sizeType) {
            switch (strtoupper($sizeType)) {
                case 'THUMB':
                    $w = 200;
                    $h = 100;
                    AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, false, true, false);
                    break;
                case 'MOBILE':
                    $w = $dimenssionArr['width'];
                    $h = $dimenssionArr['height'];
                    AttachedFile::displayImage($image_name, $w, $h, $default_image);
                    break;
                case 'TABLET':
                    $w = $dimenssionArr['width'];
                    $h = $dimenssionArr['height'];
                    AttachedFile::displayImage($image_name, $w, $h, $default_image);
                    break;
                case 'DESKTOP':
                    $w = $dimenssionArr['width'];
                    $h = $dimenssionArr['height'];
                    AttachedFile::displayImage($image_name, $w, $h, $default_image);
                    break;
                default:
                    $w = $dimenssionArr['width'];
                    $h = $dimenssionArr['height'];
                    AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, false, true, false);
                    break;
            }
        } else {
            AttachedFile::displayOriginalImage($image_name, $default_image);
        }
    }

    /* Moved in banner controller
      function banner( $banner_id, $sizeType = ''){
      $default_image = 'brand_deafult_image.jpg';
      $banner_id = FatUtility::int($banner_id);

      $file_row = AttachedFile::getAttachment( AttachedFile::FILETYPE_BANNER, $banner_id );
      $image_name = isset($file_row['afile_physical_path']) ?  $file_row['afile_physical_path'] : '';

      switch( strtoupper( $sizeType ) ){
      case 'THUMB':
      $w = 200;
      $h = 100;
      AttachedFile::displayImage( $image_name, $w, $h, $default_image );
      break;
      default:
      $w = 1320;
      $h = 440;
      AttachedFile::displayImage( $image_name, $w, $h, $default_image );
      break;
      }
      } */

    public function SocialPlatform($splatform_id, $sizeType = '')
    {
        $default_image = 'brand_deafult_image.jpg';
        $splatform_id = FatUtility::int($splatform_id);

        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_SOCIAL_PLATFORM_IMAGE, $splatform_id);
        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 200;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                $w = 30;
                $h = 30;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
        }
    }

    public function collectionReal($recordId, $langId = 0, $sizeType = '', $fileType = '')
    {
        $this->displayCollectionImage($recordId, $langId, $sizeType, true, $fileType);
    }

    public function collection($recordId, $langId = 0, $sizeType = '')
    {
        $this->displayCollectionImage($recordId, $langId, $sizeType);
    }

    public function displayCollectionImage($collectionId, $langId = 0, $sizeType = '', $displayUniversalImage = true, $fileType = '')
    {
        $collectionId = FatUtility::int($collectionId);
        $layout = Collections::getAttributesById($collectionId, 'collection_layout_type');
        $fileType = empty($fileType) ? AttachedFile::FILETYPE_COLLECTION_IMAGE : $fileType;
        //$file_row = AttachedFile::getAttachment( AttachedFile::FILETYPE_COLLECTION_IMAGE, $collectionId );
        $file_row = AttachedFile::getAttachment($fileType, $collectionId, 0, $langId, $displayUniversalImage);
        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
        
        $activeTheme = applicationConstants::getActiveTheme();
        $bannerSizeArr = imagesSizes::getBannersDimensions();
        $bannerSizeArr = (isset($bannerSizeArr[$activeTheme])) ? $bannerSizeArr[$activeTheme] : $bannerSizeArr[imagesSizes::THEME_DEFAULT];
        $bannerSizeArr = isset($bannerSizeArr[$layout]) ? $bannerSizeArr[$layout]:[];
        

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h);
                break;
            case 'HOME':
                $w = 76;
                $h = 92;
                AttachedFile::displayImage($image_name, $w, $h);
                break;
            case 'TESTIMONIAL':
                $w = 1024;
                $h = 720;
                
                if (!empty($bannerSizeArr)) {
                    $w = $bannerSizeArr[applicationConstants::SCREEN_DESKTOP]['width'];
                    $h = $bannerSizeArr[applicationConstants::SCREEN_DESKTOP]['height']; 
                }
                
                
                AttachedFile::displayImage($image_name, $w, $h);
                break;
            default:
                AttachedFile::displayOriginalImage($image_name);
                break;
        }
    }

    public function collectionBgReal($recordId, $langId = 0, $sizeType = '')
    {
        $this->displayCollectionBgImage($recordId, $langId, $sizeType, false);
    }

    public function collectionBg($recordId, $langId = 0, $sizeType = '')
    {
        $this->displayCollectionBgImage($recordId, $langId, $sizeType);
    }

    public function displayCollectionBgImage($collectionId, $langId = 0, $sizeType = '', $displayUniversalImage = true)
    {
        $collectionId = FatUtility::int($collectionId);
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_COLLECTION_BG_IMAGE, $collectionId, 0, $langId, $displayUniversalImage);
        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h);
                break;
            default:
                AttachedFile::displayOriginalImage($image_name);
                break;
        }
    }

    public function blogPostAdmin($postId, $langId = 0, $size_type = '', $subRecordId = 0, $afile_id = 0, $featuredImage = false)
    {
        $this->blogPost($postId, $langId, $size_type, $subRecordId, $afile_id, false, $featuredImage);
    }

    public function blogPostFront($postId, $langId = 0, $size_type = '', $subRecordId = 0, $afile_id = 0, $featuredImage = false)
    {
        $this->blogPost($postId, $langId, $size_type, $subRecordId, $afile_id, true, $featuredImage);
    }

    public function blogPost(int $postId, int $langId = 0, string $size_type = '', int $subRecordId = 0, int $afile_id = 0, bool $displayUniversalImage = true, bool $featuredImage = false)
    {
        $sizeArr = imagesSizes::blogImageSizeArr()[applicationConstants::getActiveTheme()];

        $default_image = 'blog-default.png';
        $fileType = AttachedFile::FILETYPE_BLOG_POST_IMAGE;

        if ($featuredImage) {
            $fileType = AttachedFile::FILETYPE_BLOG_POST_FEATURED_IMAGE;
        }

        $file_row = [];
        if ($afile_id > 0) {
            $res = AttachedFile::getAttributesById($afile_id);
            if (!false == $res && $res['afile_type'] == $fileType) {
                $file_row = $res;
            }
        } else {
            $file_row = AttachedFile::getAttachment($fileType, $postId, $subRecordId, $langId, $displayUniversalImage);
        }
        $image_name = (isset($file_row['afile_physical_path']) && trim($file_row['afile_physical_path']) != '') ? AttachedFile::FILETYPE_BLOG_POST_IMAGE_PATH . $file_row['afile_physical_path'] : '';
        switch (strtoupper($size_type)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_RESET_DIMENSIONS);
                break;
            case 'SMALL':
                $w = 200;
                $h = 200;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'LAYOUT1':
                $w = 1350;
                $h = 759;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'LAYOUT2':
                $w = 645;
                $h = 363;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'FEATURED':
                $w = 510;
                $h = 287;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'AUTOFEATURED':
                $w = 660;
                $h = 495;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'COLLECTION':
                $w = $sizeArr['width'] / 2;
                $h = $sizeArr['height'] / 2;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                $h = 400;
                $w = 400;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
        }
    }

    public function BatchProduct($prodgroup_id, $lang_id, $sizeType = '')
    {
        $prodgroup_id = FatUtility::int($prodgroup_id);
        $lang_id = FatUtility::int($lang_id);
        $default_image = '';

        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_BATCH_IMAGE, $prodgroup_id, 0, $lang_id);

        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
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

    public function testimonial($recordId, $langId = 0, $sizeType = '', $afile_id = 0, $displayUniversalImage = true)
    {
        $default_image = 'user_deafult_image.jpg';
        $recordId = FatUtility::int($recordId);
        $afile_id = FatUtility::int($afile_id);
        $langId = FatUtility::int($langId);

        if ($afile_id > 0) {
            $res = AttachedFile::getAttributesById($afile_id);
            if (!false == $res && $res['afile_type'] == AttachedFile::FILETYPE_TESTIMONIAL_IMAGE) {
                $file_row = $res;
            }
        } else {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_TESTIMONIAL_IMAGE, $recordId, 0, $langId, $displayUniversalImage);
        }
        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'MINITHUMB':
                $w = 42;
                $h = 52;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;

            case 'THUMB':
                $w = 61;
                $h = 61;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                $h = 118;
                $w = 276;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
        }
    }

    public function cpageBackgroundImage($cpageId, $langId = 0, $sizeType = '')
    {
        $cpageId = FatUtility::int($cpageId);
        $langId = FatUtility::int($langId);
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_CPAGE_BACKGROUND_IMAGE, $cpageId, 0, $langId);
        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 150;
                $h = 45;
                AttachedFile::displayImage($image_name, $w, $h);
                break;
            case 'COLLECTION_PAGE':
                $w = 45;
                $h = 41;
                AttachedFile::displayImage($image_name, $w, $h);
                break;
            default:
                AttachedFile::displayOriginalImage($image_name);
                break;
        }
    }

    public function cblockBackgroundImage($cblockId, $langId = 0, $sizeType = '', $fileType)
    {
        $cblockId = FatUtility::int($cblockId);
        $langId = FatUtility::int($langId);
        $file_row = AttachedFile::getAttachment($fileType, $cblockId, 0, $langId);
        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h, 'seller-bg.png');
                break;
            case 'DEFAULT':
                AttachedFile::displayOriginalImage($image_name, 'seller-bg.png');
                break;
        }
    }

    public function shopCollectionImage($recordId, $langId = 0, $sizeType = '', $displayUniversalImage = true)
    {
        $default_image = 'banner-default-image.png';
        $recordId = FatUtility::int($recordId);
        $langId = FatUtility::int($langId);
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_SHOP_COLLECTION_IMAGE, $recordId, 0, $langId, $displayUniversalImage);
        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'SHOP':
                $w = 600;
                $h = 338;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                AttachedFile::displayOriginalImage($image_name, $default_image);
                break;
        }
    }

    public function pushNotificationImage($pNotificationId)
    {
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_PUSH_NOTIFICATION_IMAGE, $pNotificationId);
        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
        AttachedFile::displayOriginalImage($image_name);
    }

    public function plugin($recordId, $sizeType = '', $displayUniversalImage = true)
    {
        $default_image = 'product_default_image.jpg';
        $recordId = FatUtility::int($recordId);
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_PLUGIN_LOGO, $recordId, 0, 0, $displayUniversalImage);
        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'ICON':
                $w = 30;
                $h = 30;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'MINITHUMB':
                $w = 61;
                $h = 61;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
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
            case 'MEDIUM':
                $w = 250;
                $h = 250;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                AttachedFile::displayOriginalImage($image_name, $default_image);
                break;
        }
    }

    public function productSizeChart($recordId, $sizeType, $afile_id = 0, $lang_id = 0)
    {
        $default_image = 'product_default_image.jpg';
        $recordId = FatUtility::int($recordId);
        $afile_id = FatUtility::int($afile_id);
        $lang_id = FatUtility::int($lang_id);
        if ($afile_id > 0) {
            $res = AttachedFile::getAttributesById($afile_id);
            if (!false == $res && $res['afile_type'] == AttachedFile::FILETYPE_PRODUCT_SIZE_CHART) {
                $fileRow = $res;
            }
        }

        if ($fileRow == false) {
            //echo 'sds'; die("here");
            $fileRow = AttachedFile::getAttachment(AttachedFile::FILETYPE_PRODUCT_SIZE_CHART, $recordId, -1, $lang_id);
        }

        $image_name = isset($fileRow['afile_physical_path']) ? $fileRow['afile_physical_path'] : '';
        /* CommonHelper::printArray($image_name); die();  */

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'MINI':
                $w = 50;
                $h = 50;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, false);
                break;
            case 'EXTRA-SMALL':
                $w = 60;
                $h = 60;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, false);
                break;
            case 'SMALL':
                // image size required in product listing
                $w = 230;
                $h = 230;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
            case 'MEDIUM':
                $w = 500;
                $h = 500;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
            case 'CLAYOUT3':
                $w = 230;
                $h = 230;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
            case 'CLAYOUT2':
                $w = 398;
                $h = 398;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
            case 'AUTOCLAYOUT3':
                $w = 416;
                $h = 292;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
            case 'ORIGINAL':
                $w = 500;
                $h = 500;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
            default:
                $h = 400;
                $w = 400;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
        }
    }

    public function customProductSizeChart(int $recordId, string $sizeType = '', int $afile_id = 0, int $lang_id = 0)
    {
        $default_image = 'product_default_image.jpg';
        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_CUSTOM_CATALOG_SIZE_CHART, $recordId, 0, $lang_id);
        $image_name = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'SMALL':
                // image size required in product listing
                $w = 150;
                $h = 150;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'MEDIUM':
                $w = 542;
                $h = 480;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            default:
                $h = 400;
                $w = 400;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
        }
    }

    public function productSpecFile(int $type, int $productId, int $specId, int $langId, $w = '800', $h = '800', $screen = 0)
    {
        $fileRow = AttachedFile::getAttachment($type, $productId, $specId, $langId, true, $screen);
        $image_name = isset($fileRow['afile_physical_path']) ? $fileRow['afile_physical_path'] : '';
        $fileArr = explode('.', $fileRow['afile_name']);
        $fileType = strtolower($fileArr[1]);
        $imageTypes = array('gif', 'jpg', 'jpeg', 'png', 'svg', 'bmp', 'tiff');
        if (in_array($fileType, $imageTypes)) {
            AttachedFile::displayImage($image_name, $w, $h, '', '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, false, true, false);
        } else {
            AttachedFile::downloadAttachment($image_name, $fileRow['afile_name']);
        }
    }

    public function addonProduct(int $recordId, string $sizeType, int $afileId = 0, int $langId = 0)
    {
        $default_image = 'product_default_image.jpg';
        $image_name = 'product_default_image.jpg';
        if ($afileId > 0) {
            $file_row = AttachedFile::getAttributesById($afileId);
        } else if ($recordId > 0) {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_ADDON_PRODUCT_IMAGE, $recordId, 0, $langId);
        }
        if ($file_row['afile_id'] > 0) {
            $image_name = isset($file_row['afile_physical_path']) ? AttachedFile::FILETYPE_ADDON_PRODUCT_IMAGE_PATH . $file_row['afile_physical_path'] : '';
        }
        switch (strtoupper($sizeType)) {
            case 'MINI':
                $w = 50;
                $h = 50;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, false);
                break;
            case 'THUMB':
                $w = 100;
                $h = 100;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'SMALL':
                // image size required in product listing
                $w = 150;
                $h = 150;
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'ORIGINAL':
                $w = 2000;
                $h = 2000;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
            default:
                $h = 400;
                $w = 400;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
        }
    }

    public function contentBlockIcon(int $recordId, $sizeType = '', int $afileId = 0, int $langId = 0)
    {
        $default_image = 'product_default_image.jpg';
        $file_row = false;
        if ($afileId > 0) {
            $file_row = AttachedFile::getAttributesById($afileId);
        } else {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_CONTENT_BLOCK_ICON, $recordId, 0, $langId);
        }

        if ($file_row == false) {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_CONTENT_BLOCK_ICON, $recordId, -1, $langId);
        }


        $image_name = (isset($file_row['afile_physical_path']) && !empty($file_row['afile_physical_path'])) ? $file_row['afile_physical_path'] : '';
        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 50;
                $h = 50;
                if(applicationConstants::getActiveTheme() == applicationConstants::THEME_FASHION) {
                    $w = 32;
                    $h = 32;
                }
                AttachedFile::displayImage($image_name, $w, $h, $default_image);
                break;
            case 'MINI':
                $w = 50;
                $h = 50;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, false);
                break;
            default:
                $h = 400;
                $w = 400;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
        }
    }

    public function collectionCatTmage(int $recordId, int $subRecordId, $sizeType = '', int $afileId = 0, int $langId = 0)
    {
        $default_image = 'product_default_image.jpg';
        $file_row = false;
        if ($afileId > 0) {
            $file_row = AttachedFile::getAttributesById($afileId);
        } else {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_COLLECTION_CATEGORY_IMAGE, $recordId, $subRecordId, $langId, false);
        }

        if ($file_row == false || (!empty($file_row) && $file_row['afile_id'] == -1)) {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_COLLECTION_CATEGORY_IMAGE, $recordId, $subRecordId, $langId);
        }

        $image_name = (isset($file_row['afile_physical_path']) && !empty($file_row['afile_physical_path'])) ? $file_row['afile_physical_path'] : '';
        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 50;
                $h = 50;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_RESET_DIMENSIONS);
                break;
            case 'MINI':
                $w = 50;
                $h = 50;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, false);
                break;
            case 'ORIGINAL':
                AttachedFile::displayOriginalImage($image_name);
                break;
            default:
                $h = 400;
                $w = 400;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
        }
    }

    public function attachment(int $fileId, bool $saveToTemp = false)
    {
        if ($saveToTemp) {
            $fileData = AttachedFile::getTempImagesWithCriteria(['afile_id' => $fileId], true);
        } else {
            $fileData = AttachedFile::getAttributesById($fileId);
        }
        if (!empty($fileData)) {
            $fileArr = explode('.', $fileData['afile_name']);
            $fileTypeIndex = count($fileArr) - 1;
            $fileType = strtolower($fileArr[$fileTypeIndex]);
            $imageTypes = array('gif', 'jpg', 'jpeg', 'png', 'svg', 'bmp', 'tiff');
            $image_name = isset($fileData['afile_physical_path']) ? $fileData['afile_physical_path'] : '';
            AttachedFile::downloadAttachment($image_name, $fileData['afile_name']);
        }
    }

    public function signature(int $recordId, int $subRecordId, $sizeType = '', int $afileId = 0, int $langId = 0)
    {
        $default_image = 'product_default_image.jpg';
        $file_row = false;
        if ($afileId > 0) {
            $file_row = AttachedFile::getAttributesById($afileId);
        } else {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_SIGNATURE_IMAGE, $recordId, $subRecordId, $langId, false);
        }

        if ($file_row == false || (!empty($file_row) && $file_row['afile_id'] == -1)) {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_SIGNATURE_IMAGE, $recordId, $subRecordId, $langId);
        }

        $image_name =  (isset($file_row['afile_physical_path']) && !empty($file_row['afile_physical_path'])) ? AttachedFile::FILETYPE_SIGNATURE_IMAGE_PATH . $file_row['afile_physical_path'] : '';

        switch (strtoupper($sizeType)) {
            case 'THUMB':
                $w = 300;
                $h = 300;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_RESET_DIMENSIONS);
                break;
            case 'MINI':
                $w = 50;
                $h = 50;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, false);
                break;
            case 'ORIGINAL':
                AttachedFile::displayOriginalImage($image_name);
                break;
            default:
                $h = 400;
                $w = 400;
                AttachedFile::displayImage($image_name, $w, $h, $default_image, '', ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, true);
                break;
        }
    }
    
}
