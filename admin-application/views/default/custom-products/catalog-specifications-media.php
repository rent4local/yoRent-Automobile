<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$layout = Language::getLayoutDirection($langId);
if (count($productSpecifications) > 0) {
    $specificationData = array();
    $isFile = false;
    foreach ($productSpecifications as $data) {
        //$count = 0;
        foreach ($data as $key => $value) {
            if (isset($productSpecifications['prod_spec_name'][$key]) && $productSpecifications['prod_spec_is_file'][$key] == 1) {
                $specificationData[$key] = array(
                    'prod_spec_name' => $productSpecifications['prod_spec_name'][$key],
                    'prod_spec_value' => $productSpecifications['prod_spec_value'][$key],
                    'prod_spec_is_file' => $productSpecifications['prod_spec_is_file'][$key],
                    'prod_spec_file_index' => $productSpecifications['prod_spec_file_index'][$key],
                    /* 'prod_spec_group' => isset($productSpecifications['prod_spec_group'][$key]) ? $productSpecifications['prod_spec_group'][$key] : '' */
                );
                if ($specificationData[$key]['prod_spec_is_file'] == 1) {
                    $isFile = true;
                }
            }
            //$count++;
        }
    }
    //echo '<pre>'; print_r($specificationData); echo '</pre>';
    ?>
    <?php if ($isFile) { ?>
        <div class="row" dir="<?php echo $layout; ?>">
            <div class="col-md-12">
                <div class="tablewrap">
                    <?php
                    $arr_flds = array(
                        'prod_spec_name' => Labels::getLabel('LBL_Specification_Name', $adminLangId),
                        'prod_spec_file' => Labels::getLabel('LBL_Specification_File', $adminLangId),
                        'action' => ''
                    );

                    $tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table'));
                    $th = $tbl->appendElement('thead')->appendElement('tr');
                    foreach ($arr_flds as $key => $val) {
                        if ($key == 'prodspec_name' || $key == 'prod_spec_value' || $key == 'prod_spec_group') {
                            $e = $th->appendElement('th', array('width' => '27%'), $val);
                        } else {
                            $e = $th->appendElement('th', array(), $val);
                        }
                    }


                    foreach ($specificationData as $keyData => $specification) {
                        if ($specification['prod_spec_is_file'] == 1) {
                            $tr = $tbl->appendElement('tr');
                            foreach ($arr_flds as $key => $val) {
                                $td = $tr->appendElement('td');
                                switch ($key) {
                                    case 'prod_spec_file' :
                                        $fileHtml = '';
                                        $fileData = AttachedFile::getAttachment(AttachedFile::FILETYPE_PRODUCT_REQUEST_SPECIFICATION_FILE, $preqId, $specification['prod_spec_file_index'], $langId);
                                        if (!empty($fileData)) {
                                            $fileArr = explode('.', $fileData['afile_name']);
                                            //$fileType = strtolower($fileArr[1]);
                                            $fileTypeIndex = count($fileArr) - 1;
                                            $fileType = strtolower($fileArr[$fileTypeIndex]);
                                            $imageTypes = array('gif', 'jpg', 'jpeg', 'png', 'svg', 'bmp', 'tiff');

                                            $attachmentUrl = CommonHelper::generateFullUrl('image', 'productSpecFile', array(AttachedFile::FILETYPE_PRODUCT_REQUEST_SPECIFICATION_FILE, $fileData['afile_record_id'], $fileData['afile_record_subid'], $fileData['afile_lang_id']), CONF_WEBROOT_FRONT_URL);
                                            $fileHtml = '';
                                            if (in_array($fileType, $imageTypes)) {
                                                $imageUrl = CommonHelper::generateFullUrl('image', 'productSpecFile', array(AttachedFile::FILETYPE_PRODUCT_REQUEST_SPECIFICATION_FILE, $fileData['afile_record_id'], $fileData['afile_record_subid'], $fileData['afile_lang_id'], 50, 50), CONF_WEBROOT_FRONT_URL);

                                                $fileHtml = "<a href='" . $attachmentUrl . "' target='_blank' title='" . $fileData['afile_name'] . "'><img src='" . $imageUrl . "' class='img-thumbnail image-small' /> </a>";
                                            } else {
                                                $fileHtml = "<a href='" . $attachmentUrl . "' title='" . $fileData['afile_name'] . "' download><i class='fa fa-download' aria-hidden='true'></i></a>";
                                            }
                                        }
                                        $td->appendElement('plaintext', array(), $fileHtml, true);
                                        break;

                                    case 'action':
                                        $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-sm btn-clean btn-icon btn-icon-md', 'title' => Labels::getLabel('LBL_Edit', $adminLangId), 'onClick' => 'prodSpecificationMediaSection(' . $langId . ',' . $keyData . ')'), '<i class="fa fa-edit"></i>', true);
                                        $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-sm btn-clean btn-icon btn-icon-md', 'title' => Labels::getLabel('LBL_Delete', $adminLangId), 'onClick' => 'deleteProdSpec(' . $keyData . ',' . $langId . ', 1)'), '<i class="fa fa-trash"></i>', true);
                                        break;
                                    default:
                                        $td->appendElement('plaintext', array(), $specification[$key], true);
                                        break;
                                }
                            }
                        }
                    }
                    echo $tbl->getHtml();
                    ?>
                </div>
            </div>
        </div>
    <?php } ?>
<?php } ?>

