<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form layout--' . $formLayout);
$frm->setFormTagAttribute('id', 'contentBlockFrm');
$frm->setFormTagAttribute('onsubmit', 'setupRecord(); return(false);');

$imgFld = $frm->getField('block_image');
$imgFld->addFieldTagAttribute('onChange', 'popupImageBlock(this)');
$imgFld->htmlBeforeField = '<span class="filename"></span>';
$imgFld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_Please_keep_image_dimensions_greater_than_' . $frm->getField('min_width')->value .  '_x_'. $frm->getField('min_height')->value .'', $adminLangId) . '</small>';

$minHeight = $frm->getField('min_height')->value;
$minWidth = $frm->getField('min_width')->value;

$siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
?>
<section class="section">
    <div class="sectionbody space">
        <div class="row">
            <div class="col-sm-12">
                <div class="tabs_nav_container responsive flat">
                    <ul class="tabs_nav">
                        <li><a href="javascript:void(0)" onclick="collectionForm(<?php echo $collection_type ?>, <?php echo $collection_layout_type ?>, <?php echo $collection_id ?>, 0);">
                                <?php echo Labels::getLabel('LBL_General', $adminLangId); ?></a>
                        </li>
						<?php for ($recordIndex = 1; $recordIndex <= $recordLimit; $recordIndex++) { ?>
							<li>
								<a class="<?php echo ($displayOrder == $recordIndex ) ? "active" : ""; ?>" href="javascript:void(0)" <?php if ($collection_id > 0) { ?> onclick="addRecordForm(<?php echo $collection_id ?>, <?php echo $collection_type ?>, <?php echo $recordIndex;?>);" <?php } ?>>
									<?php echo Labels::getLabel('LBL_Step_'. $recordIndex, $adminLangId); ?>
								</a>
							</li>
						<?php }  ?>
						<?php if (in_array($collection_layout_type, Collections::LAYOUT_WITH_MEDIA)) { ?>
							<li>
								<a  href="javascript:void(0)" <?php if ($collection_id > 0) { ?> onclick="collectionMediaForm(<?php echo $collection_id ?>);" <?php } ?>>
									<?php echo Labels::getLabel('LBL_Media', $adminLangId); ?>
								</a>
							</li>
                        <?php } ?>
					</ul>
                    <div class="tabs_panel_wrap">
                        <div class="tabs_panel" id="tabs_form">
                            <?php echo $frm->getFormTag(); ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="field-set">
                                        <div class="caption-wraper">
                                            <label class="field_label">
                                                <?php
                                                $fld = $frm->getField('cbs_name[' . $siteDefaultLangId . ']');
                                                echo $fld->getCaption();
                                                ?>
                                                <span class="spn_must_field">*</span></label>
                                        </div>
                                        <div class="field-wraper">
                                            <div class="field_cover">
                                                <?php echo $frm->getFieldHtml('cbs_name[' . $siteDefaultLangId . ']'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="field-set">
                                        <div class="caption-wraper">
                                            <label class="field_label">
                                                <?php
                                                $fld = $frm->getField('cbs_identifier');
                                                echo $fld->getCaption();
                                                ?>
                                                <span class="spn_must_field">*</span></label>
                                        </div>
                                        <div class="field-wraper">
                                            <div class="field_cover">
                                                <?php echo $frm->getFieldHtml('cbs_identifier'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
								<?php if ($collection_layout_type != Collections::TYPE_CONTENT_BLOCK_WITH_ICON_LAYOUT3) { ?>
                                    <div class="col-md-12">
                                        <div class="field-set pb-0">
                                            <div class="caption-wraper">
                                                <label class="field_label">
                                                    <?php
                                                    $fld = $frm->getField('block_image' );
                                                    echo $fld->getCaption();
                                                    ?>
                                                </label>
                                            </div>
                                            <div class="field-wraper">
                                                <div class="field_cover">
                                                    <?php echo $frm->getFieldHtml('block_image'); ?>
                                                </div>
                                            </div>
                                            <div id="cropperBox-js"></div>
                                            <?php  
                                            /* [ MEDIA INSTRUCTIONS START HERE */
                                            $tpl = new FatTemplate('', '');
                                            $tpl->set('adminLangId', $adminLangId);
                                            echo $tpl->render(false, false, '_partial/imageUploadInstructions.php', true, true);
                                            /* ] */    
                                            ?>
                                            
                                            <div id="imageupload_div" class="padd15"></div>
                                            <div id="cropper-dimensions" data-height="<?php echo $minHeight;?>" data-width="<?php echo $minWidth;?>">
                                            </div>
                                        </div>
                                          
                                        
                                    </div>
                                    <div class="col-md-12">
                                      
                                    </div>
                                        
								<?php } ?>

                                <div class="col-md-12">
                                    <div class="field-set">
                                        <div class="caption-wraper">
                                            <label class="field_label">
                                                <?php
                                                $fld = $frm->getField('cbslang_description_'.$siteDefaultLangId );
                                                echo $fld->getCaption();
                                                ?>
                                            </label>
                                        </div>
                                        <div class="field-wraper">
                                            <div class="field_cover">
                                                <?php echo $frm->getFieldHtml('cbslang_description_'.$siteDefaultLangId); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            
                            <?php if (!empty($otherLangData)) { ?>
                            <div class="col-md-12">
                                <?php foreach ($otherLangData as $langId => $data) { ?>
                                    <div class="accordians_container accordians_container-categories" defaultLang="<?php echo $siteDefaultLangId; ?>" language="<?php echo $langId; ?>" id="accordion-language_<?php echo $langId; ?>" onClick="translateData(this)">
                                        <div class="accordian_panel">
                                            <span class="accordian_title accordianhead accordian_title" id="collapse_<?php echo $langId; ?>">
                                                <?php
                                                echo $data . " ";
                                                echo Labels::getLabel('LBL_Language_Data', $adminLangId);
                                                ?>
                                            </span>
                                            <div class="accordian_body accordiancontent" style="display: none;">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="field-set">
                                                            <div class="caption-wraper">
                                                                <label class="field_label">
                                                                    <?php
                                                                    $fld = $frm->getField('cbs_name[' . $langId . ']');
                                                                    echo $fld->getCaption();
                                                                    ?>
                                                                </label>
                                                            </div>
                                                            <div class="field-wraper">
                                                                <div class="field_cover">
                                                                    <?php echo $frm->getFieldHtml('cbs_name[' . $langId . ']'); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="field-set">
                                                            <div class="caption-wraper">
                                                                <label class="field_label">
                                                                    <?php
                                                                    $fld = $frm->getField('cbslang_description_'.$langId );
                                                                    echo $fld->getCaption();
                                                                    ?>
                                                                </label>
                                                            </div>
                                                            <div class="field-wraper">
                                                                <div class="field_cover">
                                                                    <?php echo $frm->getFieldHtml('cbslang_description_'.$langId); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                            <div class="col-md-6">
                                <div class="field-set d-flex align-items-center">
                                    <div class="field-wraper w-auto">
                                        <div class="field_cover">
                                            <?php echo $frm->getFieldHtml('btn_submit'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <?php
                            echo $frm->getFieldHtml('min_width');
                            echo $frm->getFieldHtml('min_height');
                            echo $frm->getFieldHtml('cbs_id');
                            echo $frm->getFieldHtml('collection_id');
                            echo $frm->getFieldHtml('collection_type');
                            echo $frm->getFieldHtml('cbs_display_order');
                            ?>
                            </form>
                            <?php echo $frm->getExternalJS(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script type="text/javascript">
    $(document).ready(function () {

    });
</script>