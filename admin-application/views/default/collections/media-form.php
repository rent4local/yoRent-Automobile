<?php

use Twilio\Rest\Api\V2010\Account\ApplicationContext;

defined('SYSTEM_INIT') or die('Invalid Usage.');
$collectionMediaFrm->setFormTagAttribute('class', 'web_form');
$collectionMediaFrm->developerTags['colClassPrefix'] = 'col-sm-';
$collectionMediaFrm->developerTags['fld_default_col'] = 6;

$collectionImageDisplayDiv = $collectionMediaFrm->getField('collection_image_display_div');
$collectionImageDisplayDiv->developerTags['col'] = 12;

$languageFld = $collectionMediaFrm->getField('image_lang_id');
$languageFld->setFieldTagAttribute('class', 'language-js');

$headingArea = $collectionMediaFrm->getField('collection_image_heading');
$hideCheckClass = "hide-media-check";
$width = '1024';
$height = '720';
if (!in_array($collection_layout_type, Collections::LAYOUT_WITH_MEDIA)) {
    $str = '<small class="text--small">' . Labels::getLabel('LBL_Used_For_Mobile_Applications', $adminLangId) . '</small>';
    $headingArea->value = $str;
    $hideCheckClass = "";
    $width = '640';
    $height = '480';
}

if(!empty($dimensions)) {
    $width = $dimensions[applicationConstants::SCREEN_DESKTOP]['width'];
    $height = $dimensions[applicationConstants::SCREEN_DESKTOP]['height'];
}

$displayMediaOnlyObj = $collectionMediaFrm->getField('collection_display_media_only');
$displayMediaOnlyObj->setWrapperAttribute('class', $hideCheckClass);
$displayMediaOnlyObj->setFieldTagAttribute('class', 'displayMediaOnly--js');
$displayMediaOnlyObj->setFieldTagAttribute('onclick', 'displayMediaOnly(' . $collection_id . ', this)');
if (0 < $displayMediaOnly) {
    $displayMediaOnlyObj->setFieldTagAttribute('checked', 'checked');
}


$fld = $collectionMediaFrm->getField('collection_image');
$fld->setFieldTagAttribute('data-collection_id', $collection_id);
$fld->addFieldTagAttribute('onChange', 'popupImage(this)');
$preferredDimensionsStr = '<small class="text--small">' . sprintf(Labels::getLabel('LBL_Preferred_Dimensions_%s', $adminLangId), $width . '*' . $height) . '</small>';
$fld->htmlAfterField = $preferredDimensionsStr;



$fileTypeArr = [AttachedFile::FILETYPE_COLLECTION_IMAGE];
if ($collection_layout_type != Collections::TYPE_CATEGORY_LAYOUT4) {
    foreach ($fileTypeArr as $fileType) {
        $method = 'collectionReal';
        $cType = '';
        $fn = 'removeCollectionImage';
        if ($fileType == AttachedFile::FILETYPE_COLLECTION_BG_IMAGE) {
            $method = 'collectionBgReal';
            $cType = 'bg';
            $fn = 'removeCollectionBGImage';
        }
        $imgUpdatedOn = AttachedFile::setTimeParam($imgUpdatedOn);
        $imgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', $method, array($collection_id, 0, 'THUMB'), CONF_WEBROOT_FRONT_URL) . $imgUpdatedOn, CONF_IMG_CACHE_TIME, '.jpg');

        $imagesHtml = '<ul class="grids--onefifth ' . $cType . 'CollectionImages-js">
        <li id="' . $cType . 'Image-0">
            <div class="logoWrap">
                <div class="logothumb">
                    <img src="' . $imgUrl . '">';
        if (AttachedFile::getAttachment($fileType, $collection_id, 0, 0, false)) {
            $imagesHtml .= '<a class="deleteLink white" href="javascript:void(0);" title="Delete ' . $collectionImages['afile_name'] . '" onclick="' . $fn . '(' . $collection_id . ',0)" class="delete"><i class="ion-close-round"></i></a>';
        }

        $imagesHtml .= '</div>
                <small><strong> ' . Labels::getLabel('LBL_Language', $adminLangId) . ':</strong> ' . Labels::getLabel('LBL_All_Languages', $adminLangId) . '</small>
            </div>
        </li>';
        foreach ($languages as $langId => $langName) {
            $langImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', $method, array($collection_id, $langId, 'THUMB'), CONF_WEBROOT_FRONT_URL) . $imgUpdatedOn, CONF_IMG_CACHE_TIME, '.jpg');

            $imagesHtml .= '<li class="d-none" id="' . $cType . 'Image-' . $langId . '">
                            <div class="logoWrap">
                                <div class="logothumb">
                                    <img src="' . $langImgUrl . '">';
            if (AttachedFile::getAttachment($fileType, $collection_id, 0, $langId, false)) {
                $imagesHtml .= '<a class="deleteLink white" href="javascript:void(0);" title="Delete ' . $collectionImages['afile_name'] . '" onclick="' . $fn . '(' . $collection_id . ',' . $langId . ')" class="delete"><i class="ion-close-round"></i></a>';
            }

            $imagesHtml .= '</div>
                                <small><strong> ' . Labels::getLabel('LBL_Language', $adminLangId) . ':</strong> ' . $langName . '</small>
                            </div>
                        </li>';
        }
        $imagesHtml .= '</ul>';

        if ($fileType == AttachedFile::FILETYPE_COLLECTION_BG_IMAGE) {
            $collectionBgImageDisplayDiv->value = $imagesHtml;
        } else {
            $collectionImageDisplayDiv->value = $imagesHtml;
        }
    }
}

$collectionMediaFrm->developerTags['colClassPrefix'] = 'col-md-';
$collectionMediaFrm->developerTags['fld_default_col'] = 12;
?>
<div id="cropperBox-js"></div>
<section class="section" id="mediaForm-js">
    <div class="sectionbody space">
        <div class="row">
            <div class="col-sm-12">
                <div class="tabs_nav_container responsive flat">
                    <ul class="tabs_nav">
                        <li>
                            <a href="javascript:void(0)" onclick="collectionForm(<?php echo $collection_type ?>, <?php echo $collection_layout_type ?>, <?php echo $collection_id ?>);">
                                <?php echo Labels::getLabel('LBL_General', $adminLangId); ?>
                            </a>
                        </li>

                        <?php if ($collection_type == Collections::COLLECTION_TYPE_CONTENT_BLOCK_WITH_ICON) {
                            for ($recordIndex = 1; $recordIndex <= $recordLimit; $recordIndex++) { ?>
                                <li>
                                    <a href="javascript:void(0)" onclick="addRecordForm(<?php echo $collection_id ?>, <?php echo $collection_type ?>, <?php echo $recordIndex; ?>);">
                                        <?php echo Labels::getLabel('LBL_Step_' . $recordIndex, $adminLangId); ?></a>
                                </li>
                        <?php }
                        } ?>

                        <?php if (!in_array($collection_type, Collections::COLLECTION_WITHOUT_RECORDS) && $collection_layout_type != Collections::TYPE_CATEGORY_LAYOUT4) { ?>
                            <li>
                                <a class="" href="javascript:void(0)" onclick="recordForm(<?php echo $collection_id ?>, <?php echo $collection_type ?>);">
                                    <?php echo Labels::getLabel('LBL_Link_Records', $adminLangId); ?>
                                </a>
                            </li>
                        <?php } ?>
                        <li><a class="active" href="javascript:void(0)" onclick="collectionMediaForm(<?php echo $collection_id ?>);"><?php echo Labels::getLabel('LBL_Media', $adminLangId); ?></a></li>
                    </ul>
                    <div class="tabs_panel_wrap">
                        <div class="tabs_panel">
                            <?php 
                            /* [ MEDIA INSTRUCTIONS START HERE */
                            $tpl = new FatTemplate('', '');
                            $tpl->set('adminLangId', $adminLangId);
                            echo $tpl->render(false, false, '_partial/imageUploadInstructions.php', true, true);
                            /* ] */    
                            
                            if ($collection_layout_type == Collections::TYPE_CATEGORY_LAYOUT4) { ?>
                                <p class="note text-danger"><?php echo Labels::getLabel('LBL_All_categories_are_required_to_show_the_collection_on_frontend', $adminLangId); ?></p>
                                <hr />
                                <?php
                                echo '<table class="table"><tr><td>'. Labels::getLabel('LBL_Category_Name', $adminLangId) .'</td><td>'. Labels::getLabel('LBL_Featured_Image', $adminLangId) .'</td><td style="width : 40%;"></td></tr>';
                                
                                
                                for ($order = 1; $order <= $recordLimit; $order++) {
                                    $category = (isset($attachedCategories[$order])) ? $attachedCategories[$order] : [];
                                    $catImages = (!empty($category) && isset($collectionImages[$order])) ? $collectionImages[$order] : [];
                                ?>
                                    <tr>
                                        <td>
                                            <input data-field-caption="Categories" type="text" name="collection_records" value="<?php echo (!empty($category)) ? $category['record_title'] : ""; ?>" class="ui-autocomplete-input" autocomplete="off" data-displayorder="<?php echo $order; ?>">
                                        </td>
                                        <td>
                                            <input type="hidden" name="category_id_<?php echo $order; ?>" value="<?php echo (!empty($category)) ? $category['record_id'] : "0"; ?>">
                                            <input name="catFile_<?php echo $order; ?>" type="file" accept="image/*" onChange="popupCatImages(this, <?php echo $collection_id; ?>, <?php echo $order; ?>, <?php echo AttachedFile::FILETYPE_COLLECTION_CATEGORY_IMAGE; ?>)" />
                                            <input type="hidden" name="min_width_<?php echo $order; ?>" value="<?php echo $gridImageSizeArr[$order]['width'] ?>">
                                            <input type="hidden" name="min_height_<?php echo $order; ?>" value="<?php echo $gridImageSizeArr[$order]['height'] ?>">
                                        </td>
                                        <td style="width : 40%;">
                                            <?php if (!empty($catImages)) {
                                            ?>
                                                <ul class="collection_cat_images">
                                                    <?php foreach ($catImages as $colImage) { ?>
                                                        <li>
                                                            <div class="logoWrap">
                                                                <div class="logothumb">
                                                                    <img src="<?php echo UrlHelper::generateFullUrl('Image', 'CollectionCatTmage', array($colImage['afile_record_id'], $colImage['afile_record_subid'], 'THUMB', $colImage['afile_id'], $adminLangId), CONF_WEBROOT_FRONTEND); ?>">
                                                                </div>
                                                                <small></small>
                                                            </div>
                                                        </li>
                                                    <?php } ?>
                                                </ul>

                                            <?php }
                                            ?>
                                        </td>
                                    </tr>
                            <?php
                                }
                                echo "</table>";
                            } else {
                                echo $collectionMediaFrm->getFormHtml();
                            }
                            ?>

                        </div>
                    </div>
                </div>
            </div>
            <script>
                $('input[name=\'collection_records\']').autocomplete({
                    'classes': {
                        "ui-autocomplete": "custom-ui-autocomplete"
                    },
                    'source': function(request, response) {
                        $.ajax({
                            url: fcom.makeUrl('ProductCategories', 'autocomplete'),
                            data: {
                                keyword: request['term'],
                                fIsAjax: 1,
                                collection_id: <?php echo $collection_id; ?>,
                            },
                            dataType: 'json',
                            type: 'post',
                            success: function(json) {
                                response($.map(json, function(item) {
                                    return {
                                        label: item['name'],
                                        value: item['name'],
                                        id: item['id']
                                    };
                                }));
                            },
                        });
                    },
                    select: function(event, ul) {
                        var displayOrder = $(this).data('displayorder');
                        updateRecord(<?php echo $collection_id; ?>, ul.item.id, displayOrder);
                        $('input[name="category_id_' + displayOrder + '"]').val(ul.item.id);
                        $(this).val(ul.item.label);
                        return false;
                    }
                });
            </script>


            <script type="text/javascript">
                $('input[name=min_width]').val(<?php echo $width; ?>);
                $('input[name=min_height]').val(<?php echo $height; ?>);
                var aspectRatio = '<?php echo $width / $height; ?>';
                var FILETYPE_COLLECTION_IMAGE = '<?php echo AttachedFile::FILETYPE_COLLECTION_IMAGE ?>';
                var FILETYPE_COLLECTION_BG_IMAGE = '<?php echo AttachedFile::FILETYPE_COLLECTION_BG_IMAGE ?>';
            </script>
            <style>
                ul.collection_cat_images li {
                    display: inline-block;
                    max-width: 150px;
                    min-width: 100px;
                    list-style: none;
                }

                ul.collection_cat_images .logoWrap {
                    margin: 0px;
                }

                .hide-media-check {
                    display: none;
                }
            </style>