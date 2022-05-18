<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$productFrm->setFormTagAttribute('class', 'form form--horizontal attr-spec-frm--js');
$productFrm->setFormTagAttribute('onsubmit', 'setUpCatalogProductAttributes(this); return(false);');

$featuredFld = $productFrm->getField('product_featured');
$featuredFld->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
$featuredFld->developerTags['cbHtmlAfterCheckbox'] = '';

/* $freeShopFld = $productFrm->getField('ps_free');
  $freeShopFld->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
  $freeShopFld->developerTags['cbHtmlAfterCheckbox'] = ''; */

/* $codFld = $productFrm->getField('product_cod_enabled');
  $codFld->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
  $codFld->developerTags['cbHtmlAfterCheckbox'] = ''; */

$btnBackFld = $productFrm->getField('btn_back');
$btnBackFld->setFieldTagAttribute('onClick', 'customCatalogProductForm(' . $preqId . ');');
$btnBackFld->setFieldTagAttribute('class', "btn btn-outline-brand");
$btnBackFld->value = Labels::getLabel('LBL_Back', $siteLangId);

$btnSubmit = $productFrm->getField('btn_submit');
$btnSubmit->setFieldTagAttribute('class', "btn btn-brand");
?>
<div class="row justify-content-center">
    <div class="col-md-12">
        <?php echo $productFrm->getFormTag(); ?>
        <div class="row">
            <div class="col-md-6">
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
            <?php if(FatApp::getConfig("CONF_ALLOW_SALE", FatUtility::VAR_INT, 0)) { ?>
            <div class="col-md-6">
                <div class="field-set">
                    <div class="caption-wraper">
                        <label class="field_label">
                            <?php
                            $fld = $productFrm->getField('product_warranty');
                            echo $fld->getCaption();
                            ?>
                        </label>
                        <?php /* <span class="spn_must_field">*</span> */ ?>
                    </div>
                    <div class="field-wraper">
                        <div class="field_cover">
                            <?php echo $productFrm->getFieldHtml('product_warranty'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
            <div class="col-md-4">
                <div class="field-set">
                    <div class="caption-wraper"></div>
                    <div class="field-wraper">
                        <div class="field_cover">
                            <?php echo $productFrm->getFieldHtml('product_featured'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php /* if ($productType == Product::PRODUCT_TYPE_PHYSICAL) { ?>
              <?php
              <div class="col-md-4">
              <div class="field-set">
              <div class="caption-wraper"></div>
              <div class="field-wraper">
              <div class="field_cover">
              <?php echo $productFrm->getFieldHtml('ps_free'); ?>
              </div>
              </div>
              </div>
              </div>  ?>
              <div class="col-md-4">
              <div class="field-set">
              <div class="caption-wraper"></div>
              <div class="field-wraper">
              <div class="field_cover">
              <?php echo $productFrm->getFieldHtml('product_cod_enabled'); ?>
              </div>
              </div>
              </div>
              </div>
              <?php } */ ?>
        </div>
        <div class="specifications-form-<?php echo $siteDefaultLangId; ?>"></div>
        <div class="specifications-list-<?php echo $siteDefaultLangId; ?>"></div>

        <?php
        /* $isAutoComplete = (!empty(FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, ''))) ? 1 : 0;
          if (!empty($otherLanguages)) {
          foreach ($otherLanguages as $langId => $data) {
          ?>
          <div class="accordion my-4" id="specification-accordion-<?php echo $langId; ?>">
          <h6 class="dropdown-toggle toggle-link" data-toggle="collapse" data-target="#collapse-<?php echo $langId; ?>" aria-expanded="true" aria-controls="collapse-<?php echo $langId; ?>" onClick="displayOtherLangProdSpec(this,<?php echo $langId; ?>, <?php echo $isAutoComplete; ?>)">
          <span >
          <?php
          echo $data . " ";
          echo Labels::getLabel('LBL_Language_Specification', $siteLangId);
          ?>
          </span>
          </h6>
          <div id="collapse-<?php echo $langId; ?>" class="collapse collapse-js-<?php echo $langId; ?>" aria-labelledby="headingOne" data-parent="#specification-accordion-<?php echo $langId; ?>">
          <div class="specifications-form-<?php echo $langId; ?>"></div>
          <div class="specifications-list-<?php echo $langId; ?>"></div>
          </div>


          </div>
          <?php
          }
          } */
        ?>

        <div class="row mt-3">
            <div class="col-6">
                <div class="field-set">
                    
                    <div class="field-wraper">
                        <div class="field_cover">
                            <?php echo $productFrm->getFieldHtml('btn_back'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 text-right">
                <div class="field-set">
                    <div class="btn-group">
                        <div class="col-auto js-approval-btn"></div> 
                        <div class="field-wraper">
                            <div class="field_cover">
                                <?php
                                echo $productFrm->getFieldHtml('preq_id');
                                echo $productFrm->getFieldHtml('btn_submit');
                                ?>
                            </div>
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
    .toggle-link{
        cursor: pointer;
    }
</style>