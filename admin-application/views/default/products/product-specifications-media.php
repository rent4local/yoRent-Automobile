<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
if (count($productSpecifications) > 0) {
    ?>
    <div class="row">
        <div class="col-md-12">
            <div class="tablewrap">
                <?php
                $arr_flds = array(
                    'prodspec_name' => Labels::getLabel('LBL_File_Title', $adminLangId),
                    'prodspec_file' => '',
                    /* 'prodspec_group' => Labels::getLabel('LBL_Specification_Group', $adminLangId), */
                    'action' => Labels::getLabel('', $adminLangId)
                );

                $tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table table-bordered'));
                $th = $tbl->appendElement('thead')->appendElement('tr');
                foreach ($arr_flds as $key => $val) {
                    if ($key == 'prodspec_name' || $key == 'prodspec_value' || $key == 'prodspec_group') {
                        $e = $th->appendElement('th', array('width' => '30%'), $val);
                    } else {
                        $e = $th->appendElement('th', array(), $val);
                    }
                }

                foreach ($productSpecifications as $specification) {
                    $tr = $tbl->appendElement('tr');
                    foreach ($arr_flds as $key => $val) {
                        $td = $tr->appendElement('td');
                        switch ($key) {
                            case 'prodspec_file':
                                $fileHtml = '';
                                $prodSpecId = $specification['prodspec_id'];
                                $fileData = AttachedFile::getAttachment(AttachedFile::FILETYPE_PRODUCT_SPECIFICATION_FILE, $productId, $prodSpecId, $langId);

                                if (!empty($fileData) && $fileData['afile_id'] > 0) {
                                    $fileArr = explode('.', $fileData['afile_name']);
                                    //$fileType = strtolower($fileArr[1]);
                                    $fileTypeIndex = count($fileArr) - 1;
                                    $fileType = strtolower($fileArr[$fileTypeIndex]);
                                    $imageTypes = array('gif', 'jpg', 'jpeg', 'png', 'svg', 'bmp', 'tiff');
                                    $attachmentUrl = CommonHelper::generateFullUrl('image', 'productSpecFile', array(AttachedFile::FILETYPE_PRODUCT_SPECIFICATION_FILE, $fileData['afile_record_id'], $fileData['afile_record_subid'], $fileData['afile_lang_id']), CONF_WEBROOT_FRONT_URL);

                                    if (in_array($fileType, $imageTypes)) {
                                        $imageUrl = CommonHelper::generateFullUrl('image', 'productSpecFile', array(AttachedFile::FILETYPE_PRODUCT_SPECIFICATION_FILE, $fileData['afile_record_id'], $fileData['afile_record_subid'], $fileData['afile_lang_id'], 50, 50), CONF_WEBROOT_FRONT_URL);
                                        $fileHtml = "<a href='" . $attachmentUrl . "' target='_blank' title='" . $fileData['afile_name'] . "'><img src='" . $imageUrl . "' class='img-thumbnail image-small' /> </a>";
                                    } else {
                                        $fileHtml = "<a href='" . $attachmentUrl . "' title='" . $fileData['afile_name'] . "' download><i class='fa fa-download' aria-hidden='true'></i></a>";
                                    }
                                }
                                $td->appendElement('plaintext', array(), $fileHtml, true);

                                break;
                            case 'action':
                                $prodSpecId = $specification['prodspec_id'];
                                $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-sm btn-clean btn-icon btn-icon-md', 'title' => Labels::getLabel('LBL_Edit', $adminLangId), 'onClick' => 'prodSpecificationMediaSection(' . $langId . ',' . $prodSpecId . ')'), '<i class="fa fa-edit"></i>', true);
                                $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-sm btn-clean btn-icon btn-icon-md', 'title' => Labels::getLabel('LBL_Delete', $adminLangId), 'onClick' => 'deleteProdSpec(' . $prodSpecId . ',' . $langId . ', 1)'), '<i class="fa fa-trash"></i>', true);
                                break;
                            default:
                                $td->appendElement('plaintext', array(), $specification[$key], true);
                                break;
                        }
                    }
                }
                echo $tbl->getHtml();
                ?>
            </div>
        </div>
    </div>
<?php } ?>

<style>
    .image-small{width:50px;}
</style>