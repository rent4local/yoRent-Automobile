<?php
$attributeCount = 0;
if (count($productSpecifications) > 0 || (!empty($attributes) && FatApp::getConfig('CONF_USE_CUSTOM_FIELDS', FatUtility::VAR_INT, 0) == applicationConstants::YES)) {

?>
    <div class="product-details specific-information hidespecificationtitle">
        <h2 class="block-title"><?php echo Labels::getLabel('LBL_Specifications', $siteLangId); ?></h2>
        <?php if (count($productSpecifications) > 0) { ?>
            <ul class="information_wrapper">
                <?php
                $i = 0;
                foreach ($productSpecifications as $key => $specification) {
                    if (trim($specification['prodspec_value']) == '' && $specification['prodspec_is_file'] == 0) {
                        continue;
                    }
                    $attributeCount++;
                ?>
                    <li>
                        <span class="lable"><?php echo trim($specification['prodspec_name']) . ":"; ?></span>
                        <p>
                            <?php echo html_entity_decode($specification['prodspec_value'], ENT_QUOTES, 'utf-8'); ?>
                        </p>
                    </li>

                <?php } ?>
            </ul>
        <?php } ?>

        <?php
        if (!empty($attributes) && FatApp::getConfig('CONF_USE_CUSTOM_FIELDS', FatUtility::VAR_INT, 0) == applicationConstants::YES) {

            foreach ($attributes as $key => $attribute) {
                if ($attribute['attr_group_name'] != '') {
        ?>
                    <h6 class="h6"><?php echo $attribute['attr_group_name']; ?></h6>
                <?php
                } else { ?>
                    <h6 class="h6"><?php echo Labels::getLabel('LBL_Others', $siteLangId); ?></h6>
                <?php }
                $i = 0;
                ?>
                <ul class="information_wrapper">
                    <?php

                    foreach ($attribute['attributes'] as $attr) {

                        /* if (!isset($productCustomFields[$key][$attr['attr_fld_name']]) || $productCustomFields[$key][$attr['attr_fld_name']] == '') {
                            continue;
                        } */
                        $attributeCount++;
                    ?>
                        <li>
                            <span class="lable">
                                <?php
                                echo trim(($attr['attr_name'] != '') ? $attr['attr_name'] : $attr['attr_identifier']);
                                echo ":";
                                ?>
                            </span>
                            <p>
                                <?php
                                if (!isset($productCustomFields[$key][$attr['attr_fld_name']]) || $productCustomFields[$key][$attr['attr_fld_name']] == '') {
                                    echo Labels::getLabel('LBL_N/A', $siteLangId);
                                } else {
                                    if ($attr['attr_type'] == AttrGroupAttribute::ATTRTYPE_SELECT_BOX || $attr['attr_type'] == AttrGroupAttribute::ATTRTYPE_CHECKBOXES) {
                                        $attrOpt = explode("\n", $attr['attr_options']);
                                        $selectedOptions = $productCustomFields[$key][$attr['attr_fld_name']];
                                        $selectedOptions = explode(',', $selectedOptions);
                                        $i = 1;
                                        $itemCount = 0;
                                        if (!empty($selectedOptions)) {
                                            foreach ($selectedOptions as $option) {
                                                if (!isset($attrOpt[$option])) {
                                                    continue;
                                                }

                                                echo $attrOpt[$option] . ' ' . $attr['attr_postfix'];
                                                if ($i < count($selectedOptions)) {
                                                    echo ', ';
                                                }
                                                $i++;
                                                $itemCount++;
                                            }
                                        } else {
                                            echo Labels::getLabel('LBL_N/A', $siteLangId);
                                        }
                                        if ($itemCount == 0) {
                                            echo Labels::getLabel('LBL_N/A', $siteLangId);
                                        }
                                    } else if ($attr['attr_type'] == AttrGroupAttribute::ATTRTYPE_NUMBER) {
                                        echo intval($productCustomFields[$key][$attr['attr_fld_name']]);
                                        echo $attr['attr_postfix'];
                                    } else {
                                        echo $productCustomFields[$key][$attr['attr_fld_name']];
                                        echo $attr['attr_postfix'];
                                    }
                                }
                                ?>
                            </p>
                        </li>

                    <?php
                    } ?>
                </ul>
            <?php
            } ?>

        <?php
        }
        ?>

    </div>
<?php } ?>
<?php if (1 > $attributeCount) { ?>
    <style>
        .hidespecificationtitle {
            display: none;
        }
    </style>
<?php } ?>