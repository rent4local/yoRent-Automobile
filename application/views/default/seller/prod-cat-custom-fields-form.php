<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
if ($prodCat < 1) {
    echo Labels::getLabel('LBL_No_category_attached_with_product', $siteDefaultLangId);
    echo '<input type="hidden" name="product_id" value="' . $productId . '">';
    echo '<input type="hidden" name="preq_id" value="' . $productId . '">';
} else if (empty($prodCatAttr)) {
    echo Labels::getLabel('LBL_No_Custom_field_attached_with_product_category', $siteDefaultLangId);
    echo '<input type="hidden" name="product_id" value="' . $productId . '">';
    echo '<input type="hidden" name="preq_id" value="' . $productId . '">';
} else {
    $frm->setFormTagAttribute('class', 'form form--horizontal');
    $frm->setFormTagAttribute('onsubmit', 'setupAttrData(this); return(false);');
    $btnFld = $frm->getField('btn_submit');
    $btnFld->addFieldTagAttribute('class', 'btn btn-brand');
    ?>
    <div id="">
        <?php
        echo $frm->getFormTag();
        foreach ($updatedProdCatAttr as $grp => $prodCatAttr1) {
            ?>
            <div class="group-filed">
                <?php $grpName = ucwords(AttributeGroup::displayTitle($grp, $siteDefaultLangId)); ?>
                <div class="p-4">
                    <h4 class="form__heading"><?php echo (!empty($grpName)) ? $grpName : 'others'; ?></h4>
                    <?php
                    $textAttrArr = array();
                    $i = 0;
                    foreach ($prodCatAttr1 as $attr) {
                        if (!isset($attr[$siteDefaultLangId])) {
                            $attr[$siteDefaultLangId] = current($attr);
                            $attr[$siteDefaultLangId]['attrlang_lang_id'] = $siteDefaultLangId;
                        }
                        if ($attr[$siteDefaultLangId]['attr_type'] == AttrGroupAttribute::ATTRTYPE_TEXT) {
                            $attrFldName = 'text_attributes[' . $attr[$siteDefaultLangId]['attr_attrgrp_id'] . '][' . $siteDefaultLangId . '][' . $attr[$siteDefaultLangId]['attr_fld_name'] . ']';
                            $textAttrArr[] = $attr;
                        } elseif ($attr[$siteDefaultLangId]['attr_type'] == AttrGroupAttribute::ATTRTYPE_SELECT_BOX) {
                            $attrFldName = 'num_attributes[' . $attr[$siteDefaultLangId]['attr_attrgrp_id'] . '][' . $attr[$siteDefaultLangId]['attr_fld_name'] . ']';
                            $fld = $frm->getField($attrFldName);
                            $fld->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
                        } elseif ($attr[$siteDefaultLangId]['attr_type'] == AttrGroupAttribute::ATTRTYPE_CHECKBOXES) {
                            $attrFldName = 'num_attributes[' . $attr[$siteDefaultLangId]['attr_attrgrp_id'] . '][' . $attr[$siteDefaultLangId]['attr_fld_name'] . ']';
                            $fld = $frm->getField($attrFldName);
                            $fld->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
                            $fld->developerTags['cbHtmlAfterCheckbox'] = '';
                        } else {
                            $attrFldName = 'num_attributes[' . $attr[$siteDefaultLangId]['attr_attrgrp_id'] . '][' . $attr[$siteDefaultLangId]['attr_fld_name'] . ']';
                        }
                        ?>
                        <?php if ($i == 0 || $i % 3 == 0) { ?>
                            <div class="row">
                            <?php } ?>
                            <div class="col-md-4">
                                <div class="field-set">
                                    <div class="caption-wraper">
                                        <label class="field_label"><?php echo $frm->getField($attrFldName)->getCaption(); ?></label>
                                    </div>
                                    <div class="field-wraper">
                                        <div class="field_cover">
                                            <?php echo $frm->getFieldHtml($attrFldName); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                            $i++;
                            if ($i % 3 == 0 || count($prodCatAttr1) == $i) {
                                ?>
                            </div>
                        <?php } ?>
                    <?php } ?>
                    <?php
                    if (!empty($otherLangData) && count($textAttrArr) > 0) {
                        foreach ($otherLangData as $langId => $data) {
                            $layout = Language::getLayoutDirection($langId);
                            $i = 0;
                            $j = $grp . '-' . $langId;
                            ?>
                            <div class="accordion my-4" id="specification-accordion-<?php echo $langId; ?>">
                                <h6 class="dropdown-toggle" data-toggle="collapse" data-target="#collapse-<?php echo $j; ?>" aria-expanded="true" aria-controls="collapse-<?php echo $langId; ?>">
                                    <span>
                                        <?php echo $data . " " . Labels::getLabel('LBL_Language_Data', $siteLangId); ?>
                                    </span>
                                </h6>
                                <div id="collapse-<?php echo $j; ?>" class="collapse collapse-js-<?php echo $langId; ?>" aria-labelledby="headingOne" data-parent="#specification-accordion-<?php echo $langId; ?>">
                                    <div class="p-4 mb-4 bg-gray rounded" dir="<?php echo $layout; ?>">
                                        <?php
                                        foreach ($textAttrArr as $attr) {
                                            $attrFldName = 'text_attributes[' . $attr[$siteDefaultLangId]['attr_attrgrp_id'] . '][' . $langId . '][' . $attr[$siteDefaultLangId]['attr_fld_name'] . ']';
                                            if ($i == 0 || $i % 3 == 0) {
                                                ?> <div class="row"> <?php } ?>
                                                <div class="col-md-4">
                                                    <div class="field-set">
                                                        <div class="caption-wraper">
                                                            <label class="field_label"><?php echo $frm->getField($attrFldName)->getCaption(); ?>
                                                            </label>
                                                        </div>
                                                        <div class="field-wraper">
                                                            <div class="field_cover">
                                                                <?php echo $frm->getFieldHtml($attrFldName); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                                $i++;
                                                if ($i % 3 == 0 || count($textAttrArr) == $i) {
                                                    ?>
                                                </div>
                                            <?php } ?>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
            </div>
            <?php
        }
        echo $frm->getFieldHtml('product_id');
        echo $frm->getFieldHtml('preq_id');
        ?>
        <div class="row mt-3">
            <div class="col-6">
                <div class="field-set">
                    <div class="caption-wraper"><label class="field_label"></label></div>
                    <div class="field-wraper">
                        <div class="field_cover">
                            <?php //echo $productFrm->getFieldHtml('btn_discard');    
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 text-right">
                <div class="btn-group">
                    <div class="col-auto js-approval-btn"></div> 
                    <div class="field-set m-0">
                        <div class="field-wraper">
                            <div class="field_cover">
                                <?php echo $frm->getFieldHtml('btn_submit'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <?php echo $frm->getExternalJS(); ?>
    </div>
<?php } ?>