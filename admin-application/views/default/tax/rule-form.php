<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form');
$frm->setFormTagAttribute('onsubmit', 'setupTaxRule(this); return(false);');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;
?>
      <?php echo $frm->getFormTag(); ?>
        <div class="tax-rule-form--js">
            <div class="p-4">
               
                <div class="row">            
                  
                    <?php
                    echo $frm->getFieldHtml('taxrule_id');
                    echo $frm->getFieldHtml('taxrule_taxcat_id');
                    ?>

                    <div class="col-md-4">
                        <?php
                        $taxStrFld = $frm->getField('taxrule_taxstr_id');
                        $taxStrFld->setFieldTagAttribute("id", "taxrule_taxstr_id");
                        $taxStrFld->setFieldTagAttribute("onChange", "getCombinedTaxes(this, this.value)");
                        ?>

                        <div class="border rounded p-4  h-100">
                            <div class="form-group">
                                <label for="example-text-input" class=""><?php echo $frm->getField('taxrule_name')->getCaption(); ?></label>
                                <?php echo $frm->getFieldHtml('taxrule_name'); ?>
                            </div>
                            <div class="form-group">
                                <label for="example-text-input"><?php echo $frm->getField('trr_rate')->getCaption(); ?></label>
                                <?php echo $frm->getFieldHtml('trr_rate'); ?>
                            </div>

                            <div class="form-group">
                                <label for="example-text-input"><?php echo $frm->getField('taxrule_taxstr_id')->getCaption(); ?></label>
                                <?php echo $frm->getFieldHtml('taxrule_taxstr_id'); ?>
                            </div>                                               
                            <?php
                            $fromCountryId = 0;
                            $fromStateIds = 0;
                            $toCountryId = 0;
                            $toStateIds = [];
                            $typeId = 0;
                            if (!empty($ruleLocations)) {
                                $fromCountryId = current(array_column($ruleLocations, 'taxruleloc_from_country_id'));
                                $fromStateIds = array_values(array_column($ruleLocations, 'taxruleloc_from_state_id'));

                                $toCountryId = current(array_column($ruleLocations, 'taxruleloc_to_country_id'));
                                $toStateIds = array_values(array_column($ruleLocations, 'taxruleloc_to_state_id'));                               
                                $typeId = current(array_column($ruleLocations, 'taxruleloc_type'));
                            }
                            ?>

                        </div>
                    </div>
                    <div class="col-md-4">
                        <?php
                        $countryFld = $frm->getField('taxruleloc_from_country_id');
                        $countryFld->value = $fromCountryId;
                        $countryFld->setFieldTagAttribute('onChange', 'getCountryStates(this.value,0,\'#taxruleloc_from_state_id\')');

                        $stateFld = $frm->getField('taxruleloc_from_state_id[]');
                        $stateFld->addFieldTagAttribute('multiple', 'true');
                        $stateFld->addFieldTagAttribute('class', 'selectpicker');
                        $stateFld->setFieldTagAttribute("id", "taxruleloc_from_state_id");
                        $stateFld->value = $fromStateIds;                     
                        ?>

                        <div class="border rounded p-4  h-100">
                            <div class="form-group">
                                <label for="example-text-input" class=""><?php echo $countryFld->getCaption(); ?></label>
                                <?php echo $countryFld->getHtml(); ?>
                            </div>
                            <div class="form-group">
                                <label for="example-text-input" class=""><?php echo $stateFld->getCaption(); ?></label>
                                <?php echo $stateFld->getHtml(); ?>
                            </div>

                        </div>
                    </div>


                    <div class="col-md-4">
                        <?php
                        $countryFld = $frm->getField('taxruleloc_to_country_id');
                        $countryFld->setFieldTagAttribute("id", "taxruleloc_to_country_id");
                        $countryFld->setFieldTagAttribute('onChange', 'getCountryStatesTaxInTaxForm(this, this.value,0)');

                        $countryFld->value = $toCountryId;

                        $typeFld = $frm->getField('taxruleloc_type');
                        $typeFld->value = $typeId;

                        $stateFld = $frm->getField('taxruleloc_to_state_id[]');
                        $stateFld->addFieldTagAttribute('multiple', 'true');
                        $stateFld->addFieldTagAttribute('class', 'selectpicker');
                        $stateFld->setFieldTagAttribute("id", "taxruleloc_to_state_id");
                        $stateFld->addFieldTagAttribute('data-style', 'bg-white rounded-pill px-4 py-2 shadow-sm');
                        ?>

                        <div class="border rounded p-4  h-100">

                            <div class="form-group">
                                <label for="example-text-input" class=""><?php echo $countryFld->getCaption(); ?></label>
                                <?php echo $countryFld->getHtml(); ?>
                            </div>
                            <div class="form-group">
                                <label for="example-text-input" class=""><?php echo $typeFld->getCaption(); ?></label>
                                <?php echo $frm->getFieldHtml('taxruleloc_type'); ?>
                            </div>

                            <div class="form-group">
                                <label for="example-text-input" class=""><?php echo $stateFld->getCaption(); ?></label>
                                <?php echo $stateFld->getHtml(); ?>
                            </div>                                                
                        </div>
                    </div>                                                     
                        <div class="col-md-6 combined-tax-details--js"></div>
                   
                    <div class="col-md-12">
                        <div class="form-group mt-4">
                            <label for="example-text-input" class=""></label>
                            <?php echo $frm->getFieldHtml('btn_submit'); ?>
                            <?php 
                            $fld = $frm->getField('btn_discard');
                            $fld->addFieldTagAttribute('onclick', 'reloadList()');
                            echo $fld->getHtml(); ?>
                        </div>                                    
                    </div> 
                </div>

            </div>
        </div>
        </form>
        <?php echo $frm->getExternalJs(); ?>



<script>
    $(function () {
        $('.selectpicker').selectpicker();
        checkStatesDefault(<?php echo $fromCountryId; ?>, <?php echo json_encode($fromStateIds); ?> ,'#taxruleloc_from_state_id');
        checkStatesDefault(<?php echo $toCountryId; ?>, <?php echo json_encode($toStateIds); ?> ,'#taxruleloc_to_state_id');
        $('#taxrule_taxstr_id').trigger('change');
    });

</script>
