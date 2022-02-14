<?php 
$attributesArr = (isset($prodCatAttributes[$product['prodcat_id']])) ? $prodCatAttributes[$product['prodcat_id']] : [];
$attributeValues = (isset($prodCustomFldsData[$product['product_id']])) ? $prodCustomFldsData[$product['product_id']] : [];

if (!empty($attributesArr) && !empty($attributeValues)) { ?>
	<div class="product-detail">
		<ul>
			<?php foreach ($attributesArr as $groupId => $attributeData) {
				foreach($attributeData as $attributeVal) {
					if (isset($attributeValues[$groupId][$attributeVal['attr_fld_name']]) && !empty($attributeValues[$groupId][$attributeVal['attr_fld_name']])) { 	
						?>
						<li> 
							<span><?php echo (trim($attributeVal['attr_name']) == '') ? $attributeVal['attr_identifier'] : $attributeVal['attr_name'];?></span>
							<h5><?php 
                                if ($attributeVal['attr_type'] == AttrGroupAttribute::ATTRTYPE_SELECT_BOX) {
                                    $attrOpt = explode("\n", $attributeVal['attr_options']);
                                    $selectedOptions = $attributeValues[$groupId][$attributeVal['attr_fld_name']];
                                    $selectedOptions = explode(',', $selectedOptions);
                                    $i = 1;
                                    $itemCount = 0;
                                    if (!empty($selectedOptions)) {
                                        foreach ($selectedOptions as $option) {
                                            if (!isset($attrOpt[$option])) {
                                                continue;
                                            }
                                            echo $attrOpt[$option] . ' ' . $attributeVal['attr_postfix'];
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
                                } else {
                                    echo $attributeValues[$groupId][$attributeVal['attr_fld_name']];
                                    echo  $attributeVal['attr_postfix'];
                                }
                            ?>
                            </h5>
						</li>
						<?php
					}
				}
			} ?>
		</ul>
	</div>
<?php } ?>