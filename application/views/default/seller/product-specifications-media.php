<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$layout = Language::getLayoutDirection($langId);
$isAutoComplete = (!empty(FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, ''))) ? 1 : 0;
if (count($productSpecifications) > 0) {
    ?>
    <div class="row" dir="<?php echo $layout; ?>">
        <div class="col-md-12">
            <div class="tablewrap">
                <?php
                $arr_flds = array(
                    'prodspec_name' => Labels::getLabel('LBL_Specification_Name', $siteLangId),
                    'prodspec_file' => Labels::getLabel('LBL_Specification_File', $siteLangId),
                    /* 'prodspec_group' => Labels::getLabel('LBL_Specification_Group', $siteLangId), */
                    'action' => ''
                );

                $tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table'));
                $th = $tbl->appendElement('thead')->appendElement('tr');
                foreach ($arr_flds as $key => $val) {
                    if ($key == 'prodspec_name' || $key == 'prodspec_value' /* || $key == 'prodspec_group' */) {
                        $e = $th->appendElement('th', array('width' => '50%'), $val);
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
                                $prodSpecId = $specification['prodspec_id'];
                                $fileData = AttachedFile::getAttachment(AttachedFile::FILETYPE_PRODUCT_SPECIFICATION_FILE, $productId, $prodSpecId, $langId);
                                $fileHtml = '';
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
                                $ul = $td->appendElement('ul', array('class' => 'actions'));
                                $li = $ul->appendElement('li');
                                $li->appendElement('a', array('href' => 'javascript:void(0)', 'title' => Labels::getLabel('LBL_Edit', $siteLangId), 'onClick' => 'prodSpecificationMediaSection(' . $langId . ',' . $prodSpecId . ')'), '<i class="fa fa-edit"></i>', true);

                                if ($isAutoComplete == 0 || $siteDefaultLangId == $langId) {
                                    $lia = $li->appendElement('li');
                                    $lia->appendElement('a', array('href' => 'javascript:void(0)', 'title' => Labels::getLabel('LBL_Delete', $siteLangId), 'onClick' => 'deleteProdSpec(' . $prodSpecId . ',' . $langId . ', 1)'), '<i class="fa fa-trash"></i>', true);
                                }

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

