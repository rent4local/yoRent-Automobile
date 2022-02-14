<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php if(!empty($optionCombinations)){  ?>
   <div class="variants-wrap js-scrollable table-wrap">
       <table width="100%" class="table-fixed-header">                             
        <thead>
            <tr>
                <th width="70%"><?php echo Labels::getLabel('LBL_Variants',$siteLangId);?></th>	
                 <th width="30%"><?php echo Labels::getLabel('LBL_EAN/UPC_code',$siteLangId);?></th>
            </tr>
        </thead>
        <tbody>
            <?php     
            foreach($optionCombinations as $optionValueId=>$optionValue){
                $arr = explode('|',$optionValue);
                $key = str_replace('|',',',$optionValueId); 
                $variant = $optionValue;
                /* $variant = '';
                foreach($arr as $key2=>$val){	
                    if($key2 == 0){
                        $variant = $val;
                    }else{
                        $variant = $variant." / ".$val;
                    }						
                } */ 
            ?>
            <tr>
                <td width="70%"><?php echo $variant; ?></td>	
                <td width="30%">
                    <div class="field-set">
                        <div class="field-wraper">
                            <div class="field_cover">
                                <input type="text" id="code<?php echo $optionValueId; ?>" name="code<?php echo $optionValueId?>" value="<?php echo (isset   ($upcCodeData[$optionValueId]))?$upcCodeData[$optionValueId]:'';?>" onBlur="updateUpc('<?php echo $preqId; ?>','<?php echo $optionValueId;?>')" class="form-control">
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            <?php } ?>	
        </tbody>
    </table></div>
<?php } else { ?>
    <div class="row mt-2">
        <div class="col-md-12">
            <div class="field-set">
                <div class="caption-wraper"><label class="field_label"><?php echo Labels::getLabel('LBL_EAN/UPC_code', $siteLangId); ?></label></div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <input type="text" id="code0" name="code0" value="<?php echo (isset($upcCodeData[0]['upc_code'])) ? $upcCodeData[0]['upc_code'] : ''; ?>" onBlur="updateUpc('<?php echo $productId; ?>','0')"  class="form-control">
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>