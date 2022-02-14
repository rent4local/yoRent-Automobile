<?php if (0 < count($arr_listing)) { ?>
    <div class="tax-rules">      
        <ul>
            <?php foreach ($arr_listing as $sn => $row) {
                    $ruleSpecificCombinedData = $combinedData[$row['taxrule_id']] ?? [];
                
                ?>
                <li>      
                    <?php if($canEdit) { ?>
                        <div class="actions float-right"><a onclick="editRule(<?php echo $row['taxrule_id']; ?>)" href="javascript:void(0);" title="Edit"><i class="fa fa-edit"></i></a></div>
                    <?php } ?>
                    
                    <h5  class="title"><?php echo Labels::getLabel('LBL_Rule', $siteLangId); ?>:
                        <?php echo $row['taxrule_name']; ?>
                        <span class=""><?php echo Labels::getLabel('LBL_Tax_Rate(%)', $siteLangId); ?>: <?php echo !empty($row['user_rule_rate']) ? "<del>".$row['trr_rate']."</del>" : $row['trr_rate']; ?></del>&nbsp; <?php echo $row['user_rule_rate']; ?>
                        </span></h5>
                    <ul class="tax-rules__states">   
                        <li>
                            <div class="stats">
                                <p></p>
                            </div>
                        </li>
                        
                        <?php if (!empty($ruleSpecificCombinedData) && $row['taxstr_is_combined'] > 0) { ?>
                            <li>
                                <div class="stats">
                                    <h6 class="title-sub"> <?php echo Labels::getLabel('LBL_Combined_Taxes(%)', $siteLangId); ?></h6>
                                </div>
                            </li>
                            <?php
                            foreach ($ruleSpecificCombinedData as $comData) { ?>
                                <li>
                                    <div class="stats">
                                        <p><span class="lable"><?php echo $comData['taxstr_name']; ?>:
                                            </span>
                                            
                                            
                                            <?php echo !empty($comData['user_rate']) ? "<del>".$comData['taxruledet_rate']."</del>" : $comData['taxruledet_rate']; ?></del>&nbsp; <?php echo $comData['user_rate']; ?></p>
                                    </div>
                                </li>
                            <?php }
                        } else {
                            ?>
                            <li>
                                <div class="stats">
                                    <p><span class="lable"><?php echo Labels::getLabel('LBL_Tax_Name', $siteLangId); ?>:
                                        </span><?php echo $row['taxstr_name']; ?></p>
                                </div>
                            </li>
                        <?php } ?>
                        <li>
                            <div class="stats">
                                <p><span class="lable"><?php echo Labels::getLabel('LBL_FROM_COUNTRY', $siteLangId); ?>:
                                    </span><?php echo $row['from_country'] ?? Labels::getLabel('LBL_Rest_of_the_world', $siteLangId); ?>
                                </p>
                            </div>
                        </li>
                        <li>
                            <div class="stats">
                                <p><span class="lable"><?php echo Labels::getLabel('LBL_FROM_STATES', $siteLangId); ?>:
                                    </span>
                                    <?php echo $row['from_state'] ?? Labels::getLabel('LBL_ALL_STATES', $siteLangId); ?>
                                </p>
                            </div>
                        </li>
                        <li>
                            <div class="stats">
                                <p><span class="lable"><?php echo Labels::getLabel('LBL_TO_COUNTRY', $siteLangId); ?>:
                                    </span><?php echo $row['to_country'] ?? Labels::getLabel('LBL_Rest_of_the_world', $siteLangId); ?>
                                </p>
                            </div>
                        </li>
                        <li>
                            <div class="stats">
                                <p><span class="lable"><?php echo Labels::getLabel('LBL_TO_STATES', $siteLangId); ?>:
                                    </span><?php 
                                    $taxRulesOptions = TaxRule::getTypeOptions($siteLangId);
                                    
                                    echo (isset($taxRulesOptions[$row['taxruleloc_type']])) ? $taxRulesOptions[$row['taxruleloc_type']] : ""; ?><br>
                                    <?php echo $row['to_state'] ?>
                                </p>
                            </div>
                        </li>
                    </ul>

                </li>


            <?php } ?>

        </ul>
    </div>
    <?php
} else {
    $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId, 'message' => $message));
}

$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmSearchPaging'));

$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'siteLangId' => $siteLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
