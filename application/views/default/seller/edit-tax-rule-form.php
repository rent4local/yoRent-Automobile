<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'form form_horizontal');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;
$frm->setFormTagAttribute('onsubmit', 'updateTaxRule(this); return(false);');

$btnSubmit = $frm->getField('btn_submit');
$btnSubmit->setFieldTagAttribute('class', "btn btn-brand");
?>
<div class="modal-dialog modal-dialog-centered" role="document" id="edit-tax-rule">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><?php echo Labels::getLabel('LBL_Edit_Tax_Rule', $siteLangId); ?></h5>

            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <?php echo $frm->getFormTag(); ?>
            <div class="row">
                <?php
                echo $frm->getFieldHtml('taxrule_id');

                $fld = $frm->getField('trr_rate');
                ?>
                <div class="col-md-12">
                    <div class="field-set">
                        <div class="caption-wraper">
                            <label class="field_label"><?php echo $fld->getCaption() ?><span class="spn_must_field">*</span></label>
                        </div>
                        <div class="field-wraper">
                            <div class="field_cover">
                                <?php echo $fld->getHTML('trr_rate'); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                foreach ($combinedTaxData as $key => $tax) { ?>
                    <div class="col-md-12">
                        <div class="field-set">
                            <div class="caption-wraper">
                                <label class="field_label"><?php echo $tax['taxstr_name'] ?><span class="spn_must_field">*</span></label>
                            </div>
                            <div class="field-wraper">
                                <div class="field_cover">
                                    <input type="text" data-field-caption="<?php echo $tax['taxstr_name'] ?>" name="combinedTaxDetails[<?php echo $key; ?>][taxruledet_rate]" class='combinationInput--js' value="<?php echo $tax['taxruledet_rate']; ?>">
                                    <input type="hidden" name="combinedTaxDetails[<?php echo $key; ?>][taxruledet_taxstr_id]" value="<?php echo $tax['taxruledet_taxstr_id']; ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <div class="col-md-12">
                    <div class="field-set">
                        <div class="caption-wraper"><label class="field_label">

                            </label>
                        </div>
                        <div class="field-wraper">
                            <div class="field_cover">
                                <?php echo $frm->getFieldHtml('btn_submit'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </form>
            <?php echo $frm->getExternalJs(); ?>

        </div>
    </div>
</div>

<script>
    $(function() {
        var reqValidstr = $('form[name="frmTaxRule"] input[name="trr_rate"]').attr('data-fatreq');

        $('.combinationInput--js').each(function() {
            $(this).attr('data-fatreq', reqValidstr);
        })
    });
</script>