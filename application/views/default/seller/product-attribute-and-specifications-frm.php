<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$productFrm->setFormTagAttribute('class', 'form form--horizontal attr-spec-frm--js');
$productFrm->setFormTagAttribute('onsubmit', 'setUpProductAttributes(this); return(false);');

$fld = $productFrm->getField('product_featured');
$fld->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
$fld->developerTags['cbHtmlAfterCheckbox'] = '';

$btnBackFld = $productFrm->getField('btn_back');
$btnBackFld->setFieldTagAttribute('class', "btn btn-outline-brand");
$btnBackFld->setFieldTagAttribute('onClick', 'customProductForm(' . $productId . ');');

$btnSubmit = $productFrm->getField('btn_submit');
$btnSubmit->setFieldTagAttribute('class', "btn btn-brand");
?>
<div class="row justify-content-center">
    <div class="col-md-12">
        <?php echo $productFrm->getFormTag(); ?>
        <div class="row">
            <div class="col-md-4">
                <div class="field-set">
                    <div class="caption-wraper">
                        <label class="field_label">
                            <?php
                            $fld = $productFrm->getField('product_model');
                            echo $fld->getCaption();
                            ?>
                        </label>
                        <?php if (FatApp::getConfig("CONF_PRODUCT_MODEL_MANDATORY", FatUtility::VAR_INT, 1)) { ?>
                        <span class="spn_must_field">*</span>
                        <?php } ?>
                    </div>
                    <div class="field-wraper">
                        <div class="field_cover">
                            <?php echo $productFrm->getFieldHtml('product_model'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="field-set">
                    <div class="caption-wraper">
                        <label class="field_label">
                            <?php
                            $fld = $productFrm->getField('product_warranty');
                            echo $fld->getCaption();
                            ?>
                        </label>
                        <span class="spn_must_field">*</span>
                    </div>
                    <div class="field-wraper">
                        <div class="field_cover">
                            <?php echo $productFrm->getFieldHtml('product_warranty'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="field-set">
                    <div class="caption-wraper"></div>
                    <div class="field-wraper">
                        <div class="field_cover">
                            <?php echo $productFrm->getFieldHtml('product_featured'); ?>
                            <span><i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="<?php echo Labels::getLabel('LBL_Select_the_checkbox_to_display_this_product_in_the_Featured_Product_list_on_the_homepage_at_front-end.', $siteLangId); ?>"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="specifications-form-<?php echo $siteDefaultLangId; ?>"></div>
        <div class="specifications-list-<?php echo $siteDefaultLangId; ?>"></div>

        <?php
        /* $isAutoComplete = (!empty(FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, ''))) ? 1 : 0; */

        /* if (!empty($otherLanguages)) {
          foreach ($otherLanguages as $langId => $data) {
          ?>
        <div class="accordion my-4" id="specification-accordion-<?php echo $langId; ?>">
            <h6 class="dropdown-toggle toggle-link" data-toggle="collapse"
                data-target="#collapse-<?php echo $langId; ?>" aria-expanded="true"
                aria-controls="collapse-<?php echo $langId; ?>"
                onClick="displayOtherLangProdSpec(this,<?php echo $langId; ?>, <?php echo $isAutoComplete;?>)">
                <span>
                    <?php
          echo $data . " ";
          echo Labels::getLabel('LBL_Language_Specification', $siteLangId);
          ?>
                </span>
            </h6>
            <div id="collapse-<?php echo $langId; ?>" class="collapse collapse-js-<?php echo $langId; ?>"
                aria-labelledby="headingOne" data-parent="#specification-accordion-<?php echo $langId; ?>">
                <div class="specifications-form-<?php echo $langId; ?>"></div>
                <div class="specifications-list-<?php echo $langId; ?>"></div>
            </div>
        </div>
        <?php
          }
          } */
        ?>

        <div class="row">
            <div class="col-6">
                <div class="field-set">
                    <div class="caption-wraper"><label class="field_label"></label></div>
                    <div class="field-wraper">
                        <div class="field_cover">
                            <?php echo $productFrm->getFieldHtml('btn_back'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 text-right">
                <div class="field-set">
                    <div class="caption-wraper"><label class="field_label"></label></div>
                    <div class="field-wraper">
                        <div class="field_cover">
                            <?php
                            echo $productFrm->getFieldHtml('product_id');
                            echo $productFrm->getFieldHtml('btn_submit');
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </form>
        <?php echo $productFrm->getExternalJS(); ?>
    </div>
</div>

<script type="text/javascript">
prodSpecificationSection(<?php echo $siteDefaultLangId; ?>)
prodSpecificationsByLangId(<?php echo $siteDefaultLangId; ?>)
</script>
<style>
.toggle-link {
    cursor: pointer;
}
</style>