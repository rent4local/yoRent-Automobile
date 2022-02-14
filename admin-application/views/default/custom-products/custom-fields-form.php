<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
if ($prodCat < 1) {
    echo Labels::getLabel('LBL_No_category_attached_with_product', $siteDefaultLangId);
    echo '<input type="hidden" name="product_id" value="' . $preqId . '">';
} else if (empty($prodCatAttr)) {
    echo Labels::getLabel('LBL_No_Custom_field_attached_with_product_category', $siteDefaultLangId);
    echo '<input type="hidden" name="product_id" value="' . $preqId . '">';
} else {
    $frm->setFormTagAttribute('class', 'web_form form__horizontal');
    $frm->setFormTagAttribute('onsubmit', 'setupCustomFields(this); return(false);');
    $frm->developerTags['colClassPrefix'] = 'col-md-';
    $frm->developerTags['fld_default_col'] = 12;
    $backFld = $frm->getField('btn_back');
    $backFld->addFieldTagAttribute('onclick', '$(".tabs_004").trigger("click")');
    echo $frm->getFormTag();
    foreach ($updatedProdCatAttr as $grp => $prodCatAttr1) {
        ?>
        <div class="group-filed">
            <?php $grpName = ucwords(AttributeGroup::displayTitle($grp, $siteDefaultLangId)); ?>
            <h3 class="form__heading"><?php echo (!empty($grpName)) ? $grpName : 'others'; ?></h3>
            <div class="p-4">
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
                        $fld->setWrapperAttribute('class', 'list-inline');
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
                <!-- BOC Other Language form fields -->
                <?php
                if (!empty($otherLangData) && count($textAttrArr) > 0) {
                    foreach ($otherLangData as $langId => $data) {
                        $i = 0;
                        ?>
                        <div class="accordians_container accordians_container-categories" defaultLang="<?php echo $siteDefaultLangId; ?>" language="<?php echo $langId; ?>" id="accordion-language_<?php echo $langId; ?>">
                            <div class="accordian_panel">
                                <span class="accordian_title accordianhead accordian_title" id="collapse_<?php echo $langId; ?>">
                                    <?php
                                    echo $data . " ";
                                    echo Labels::getLabel('LBL_Language_Data', $siteDefaultLangId);
                                    ?>
                                </span>
                                <div class="accordian_body accordiancontent" style="display: none;">
                                    <?php
                                    foreach ($textAttrArr as $attr) {
                                        $attrFldName = 'text_attributes[' . $attr[$siteDefaultLangId]['attr_attrgrp_id'] . '][' . $langId . '][' . $attr[$siteDefaultLangId]['attr_fld_name'] . ']';
                                        ?>
                                        <?php if ($i == 0 || $i % 3 == 0) { ?>
                                            <div class="row">
                                            <?php } ?>
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
                echo $frm->getFieldHtml('product_id');
                echo $frm->getFieldHtml('preq_id');
                ?>
            </div>
        </div>
    <?php } ?>
    <div class="row">
        <div class="col-md-6">
            <?php echo $frm->getFieldHtml('btn_back'); ?>
        </div>
        <div class="col-md-6 text-right">
            <?php echo $frm->getFieldHtml('btn_submit'); ?>
        </div>
    </div>
    </form>
    <?php echo $frm->getExternalJS(); ?>

<?php } ?>