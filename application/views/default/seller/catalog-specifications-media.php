<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$layout = Language::getLayoutDirection($siteLangId);
if (count($productSpecifications) > 0) { ?>
    <div class="row" dir="<?php echo $layout; ?>">
            <div class="col-md-12">
                <div class="tablewrap">
                    <?php
                    $arr_flds = array(
                        'prod_spec_name' => Labels::getLabel('LBL_Specification_Name', $siteLangId),
                        'prod_spec_file' => Labels::getLabel('LBL_Specification_File', $siteLangId),
                        /* 'prod_spec_group' => Labels::getLabel('LBL_Specification_Group', $siteLangId), */
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

                    foreach ($productSpecifications as $keyData => $specification) {
                        $tr = $tbl->appendElement('tr');
                            foreach ($arr_flds as $key => $val) {
                                $td = $tr->appendElement('td');
                                switch ($key) {
                                    case 'prod_spec_file' :
                                        $fileHtml = '';
                                        $fileData = [];
                                        if (FatUtility::int($specification['prod_spec_file_index']) > 0) {
                                            $fileData = AttachedFile::getAttachment(AttachedFile::FILETYPE_PRODUCT_REQUEST_SPECIFICATION_FILE, $preqId, $specification['prod_spec_file_index'], $siteLangId);
                                        } 
                                        
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
                                        $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-sm btn-clean btn-icon btn-icon-md', 'title' => Labels::getLabel('LBL_Edit', $siteLangId), 'onClick' => 'prodSpecificationMediaSection(' . $langId . ',' . $keyData . ')'), '<i class="fa fa-edit"></i>', true);
                                        $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-sm btn-clean btn-icon btn-icon-md', 'title' => Labels::getLabel('LBL_Delete', $siteLangId), 'onClick' => 'deleteProdSpec(' . $keyData . ',' . $langId . ', 1)'), '<i class="fa fa-trash"></i>', true);
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

