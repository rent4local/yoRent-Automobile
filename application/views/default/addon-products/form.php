<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$form->setFormTagAttribute('onsubmit', 'setupAddonProduct(this); return(false);');

$form->setFormTagAttribute('class', 'form');
$form->developerTags['colClassPrefix'] = 'col-md-';
$form->developerTags['fld_default_col'] = 6;

$autoUpdateFld = $form->getField('auto_update_other_langs_data');
$autoUpdateFld->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
$autoUpdateFld->developerTags['cbHtmlAfterCheckbox'] = '';

$cancelFld = $form->getField('is_eligible_cancel');
$cancelFld->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
$cancelFld->developerTags['cbHtmlAfterCheckbox'] = '';

$refundFld = $form->getField('is_eligible_refund');
$refundFld->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
$refundFld->developerTags['cbHtmlAfterCheckbox'] = '';

$btnFld = $form->getField('btn_submit');
$btnFld->addFieldTagAttribute('class', 'btn btn-brand');

$btnDiscardFld = $form->getField('btn_discard');
$btnDiscardFld->addFieldTagAttribute('class', 'btn btn-outline-brand');
$btnDiscardFld->addFieldTagAttribute('onClick', 'addonProductsList();');
$btnDiscardFld->value = Labels::getLabel('LBL_Discard', $siteLangId);

$this->includeTemplate('_partial/seller/sellerDashboardNavigation.php');
?>

<main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
        <div class="content-header row">
            <div class="col">
                <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Rental_Addons_Setup', $siteLangId); ?>
                </h2>
            </div>

            <div class="col-auto">
                <div class="btn-group">
                    <a class="btn btn-outline-brand btn-sm"
                        title="<?php echo Labels::getLabel('LBL_Back_to_Rental_Addons', $siteLangId); ?>"
                        href="<?php echo CommonHelper::generateUrl('AddonProducts'); ?>"><?php echo Labels::getLabel('LBL_Back_to_Rental_Addons', $siteLangId); ?></a>
                </div>
            </div>
        </div>
        <div class="content-body">
            <div class="tabs">
                <ul class="tabs_nav-js">
                    <li class="is-active">
                        <a class="tabs_001" rel="tabs_001" href="javascript:void(0)">
                            <?php echo Labels::getLabel('LBL_Initial_Setup', $siteLangId); ?> <i
                                class="tabs-icon fa fa-info-circle" data-toggle="tooltip" data-placement="right"
                                title="<?php echo Labels::getLabel('LBL_Setup_Basic_Details', $siteLangId); ?>">
                            </i>
                        </a>
                    </li>
                    <li>
                        <a rel="tabs_002" <?php if ($addonId > 0) { ?> class="tabs_002" <?php } ?>
                            href="javascript:void(0)">
                            <?php echo Labels::getLabel('LBL_Media', $siteLangId); ?>
                            <i class="tabs-icon fa fa-info-circle" data-toggle="tooltip" data-placement="right"
                                title="<?php echo Labels::getLabel('LBL_Add_Media', $siteLangId); ?>"></i>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="replaced">
                                <!-- [ tabs container section satrt  -->
                                <div class="tabs__content">
                                    <div id="tabs_001" class="tabs_panel" style="display: block;">
                                        <div class="row justify-content-center">
                                            <div class="col-md-12">
                                                <?php echo $form->getFormTag(); ?>
                                                <?php $divLayout = Language::getLayoutDirection($siteDefaultLangId); ?>
                                                <div class="mb-4" dir="<?php echo $divLayout; ?>"> 
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="field-set">
                                                                <div class="caption-wraper">
                                                                    <label class="field_label">
                                                                        <?php
                                                                        $fld = $form->getField('addon_identifier');
                                                                        echo $fld->getCaption();
                                                                        ?>
                                                                        <span class="spn_must_field">*</span>
                                                                    </label>
                                                                </div>
                                                                <div class="field-wraper">
                                                                    <div class="field_cover">
                                                                        <?php echo $form->getFieldHtml('addon_identifier'); ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    
                                                    
                                                        <div class="col-md-4">
                                                            <div class="field-set">
                                                                <div class="caption-wraper">
                                                                    <label class="field_label">
                                                                        <?php
                                                                        $fld = $form->getField('addonprod_title[' . $siteDefaultLangId . ']');
                                                                        echo $fld->getCaption();
                                                                        ?>
                                                                        <span class="spn_must_field">*</span>
                                                                    </label>
                                                                </div>
                                                                <div class="field-wraper">
                                                                    <div class="field_cover">
                                                                        <?php echo $form->getFieldHtml('addonprod_title[' . $siteDefaultLangId . ']'); ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="field-set">
                                                                <div class="caption-wraper">
                                                                    <label class="field_label">
                                                                        <?php
                                                                        $fld = $form->getField('addonprod_price');
                                                                        echo $fld->getCaption();
                                                                        ?>
                                                                    </label>
                                                                    <span class="spn_must_field">*</span>
                                                                </div>
                                                                <div class="field-wraper">
                                                                    <div class="field_cover">
                                                                        <?php echo $form->getFieldHtml('addonprod_price'); ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row" style="align-items: center;">
                                                        <div class="col-md-4">
                                                            <div class="field-set">
                                                                <div class="caption-wraper">
                                                                    <label class="field_label">
                                                                        <?php
                                                                        $fld = $form->getField('taxcat_name');
                                                                        echo $fld->getCaption();
                                                                        ?>
                                                                    </label>
                                                                    <?php /* <span class="spn_must_field">*</span> */ ?>
                                                                </div>
                                                                <div class="field-wraper">
                                                                    <div class="field_cover">
                                                                        <?php echo $form->getFieldHtml('taxcat_name'); ?>
                                                                        <small>
                                                                            <a class="form-text text-muted"
                                                                                target="_blank"
                                                                                href="<?php echo UrlHelper::generateUrl('seller', 'taxCategories'); ?>">
                                                                                <?php echo Labels::getLabel('LBL_Tax_Categories', $siteLangId); ?>
                                                                            </a>
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    
                                                    
                                                        <div class="col-md-4">
                                                            <div class="field-set mb-0">
                                                                <div class="caption-wraper"></div>
                                                                <div class="field-wraper">
                                                                    <div class="field_cover">
                                                                        <?php echo $form->getFieldHtml('is_eligible_cancel'); ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="col-md-4">
                                                            <div class="field-set mb-0">
                                                                <div class="caption-wraper"></div>
                                                                <div class="field-wraper">
                                                                    <div class="field_cover">
                                                                        <?php echo $form->getFieldHtml('is_eligible_refund'); ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="field-set mb-0">
                                                                <div class="caption-wraper">
                                                                    <label class="field_label">
                                                                        <?php
                                                                        $fld = $form->getField('addonprod_description_' . $siteDefaultLangId);
                                                                        echo $fld->getCaption();
                                                                        ?>
                                                                    </label>
                                                                </div>
                                                                <div class="field-wraper">
                                                                    <div class="field_cover">
                                                                        <?php echo $form->getFieldHtml('addonprod_description_' . $siteDefaultLangId); ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php
                                                    $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
                                                    if (!empty($translatorSubscriptionKey) && count($otherLanguages) > 0) {
                                                        ?>
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="field-set mb-0">
                                                                <div class="caption-wraper"></div>
                                                                <div class="field-wraper">
                                                                    <div class="field_cover">
                                                                        <?php echo $form->getFieldHtml('auto_update_other_langs_data'); ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php } ?>
                                                </div>

                                                <?php
                                                if (!empty($otherLanguages)) {
                                                    foreach ($otherLanguages as $langId => $data) {
                                                        $layout = Language::getLayoutDirection($langId);
                                                        ?>
                                                <div class="accordion my-4"
                                                    id="specification-accordion-<?php echo $langId; ?>">
                                                    <h6 class="dropdown-toggle" data-toggle="collapse"
                                                        data-target="#collapse-<?php echo $langId; ?>"
                                                        aria-expanded="true"
                                                        aria-controls="collapse-<?php echo $langId; ?>"><span
                                                            onclick="translateData(this, '<?php echo $siteDefaultLangId; ?>', '<?php echo $langId; ?>')">
                                                            <?php echo $data . " " . Labels::getLabel('LBL_Language_Data', $siteLangId); ?>
                                                        </span>
                                                    </h6>
                                                    <div id="collapse-<?php echo $langId; ?>"
                                                        class="collapse collapse-js-<?php echo $langId; ?>"
                                                        aria-labelledby="headingOne"
                                                        data-parent="#specification-accordion-<?php echo $langId; ?>">
                                                        <div class="p-4 mb-4 bg-gray rounded"
                                                            dir="<?php echo $layout; ?>">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="field-set">
                                                                        <div class="caption-wraper">
                                                                            <label class="field_label">
                                                                                <?php
                                                                                        $fld = $form->getField('addonprod_title[' . $langId . ']');
                                                                                        echo $fld->getCaption();
                                                                                        ?>
                                                                            </label>
                                                                        </div>
                                                                        <div class="field-wraper">
                                                                            <div class="field_cover">
                                                                                <?php echo $form->getFieldHtml('addonprod_title[' . $langId . ']'); ?>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <div class="field-set mb-0">
                                                                        <div class="caption-wraper">
                                                                            <label class="field_label">
                                                                                <?php
                                                                                        $fld = $form->getField('addonprod_description_' . $langId);
                                                                                        echo $fld->getCaption();
                                                                                        ?>
                                                                            </label>
                                                                        </div>
                                                                        <div class="field-wraper">
                                                                            <div class="field_cover">
                                                                                <?php echo $form->getFieldHtml('addonprod_description_' . $langId); ?>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                                    }
                                                }
                                                ?>

                                                <div class="row">
                                                    <div class="col-6">
                                                        <div class="field-set">
                                                            <div class="caption-wraper"><label
                                                                    class="field_label"></label></div>
                                                            <div class="field-wraper">
                                                                <div class="field_cover">
                                                                    <?php echo $form->getFieldHtml('btn_discard'); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-6 text-right">
                                                        <div class="field-set">
                                                            <div class="caption-wraper"><label
                                                                    class="field_label"></label></div>
                                                            <div class="field-wraper">
                                                                <div class="field_cover">
                                                                    <?php
                                                                    echo $form->getFieldHtml('ptt_taxcat_id');
                                                                    echo $form->getFieldHtml('addonprod_id');
                                                                    echo $form->getFieldHtml('btn_submit');
                                                                    ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                </form>
                                                <?php echo $form->getExternalJS(); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="tabs_002" class="tabs_panel" style="display: none;">
                                        <div id="media-form-js"></div>
                                        <div id="media-listing-js"></div>
                                    </div>
                                </div>
                                <!-- ] -->

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script type="text/javascript">
function addonProductsList() {
    window.location = fcom.makeUrl('AddonProducts');
}

function setupAddonProduct(frm) {
    let getFrm = $('#addonProductsForm')[0];
    let validator = $(getFrm).validation({
        errordisplay: 3
    });
    validator.validate();
    if (!validator.isValid())
        return;

    let data = fcom.frmData(getFrm);
    var addonId = $('input[name = "addonprod_id"]').val();
    fcom.updateWithAjax(fcom.makeUrl('AddonProducts', 'setup'), data, function(ans) {
        if (ans.status == 1) {
            if (addonId == 0) {
                $('input[name = "addonprod_id"]').val(ans.addonId);
                $('a[rel="tabs_002"]').addClass('tabs_002');
                window.history.pushState({}, '', fcom.makeUrl('AddonProducts', 'form', [ans.addonId]));
                return;
            }
            $('.tabs_002').trigger('click');
            return;
        }
    });
}

function mediaListing() {
    let addon_prod_id = $('input[name="addonprod_id"]').val();
    fcom.ajax(fcom.makeUrl('AddonProducts', 'mediaListing'), 'addon_prod_id=' + addon_prod_id, function(res) {
        $('#media-listing-js').html(res);
    });
}

$(document).on('click', '.tabs_001', function() {
    $(".tabs_nav-js  > li").removeClass('is-active');
    $("a[rel='tabs_001']").parent().addClass('is-active');
    $('#tabs_002').hide();
    $('#tabs_001').show();
});

$(document).on('click', '.tabs_002', function() {
    $(".tabs_nav-js  > li").removeClass('is-active');
    $("a[rel='tabs_002']").parent().addClass('is-active');
    fcom.ajax(fcom.makeUrl('AddonProducts', 'mediaForm'), [], function(res) {
        $('#media-form-js').html(res);
        mediaListing();
    });
    $('#tabs_001').hide();
    $('#tabs_002').show();
});

function deleteImage(addonProdId, image_id) {
    let agree = confirm(langLbl.confirmDelete);
    if (!agree) {
        return false;
    }
    fcom.ajax(fcom.makeUrl('addonProducts', 'deleteImage', [addonProdId, image_id]), '', function(t) {
        let ans = $.parseJSON(t);
        $.mbsmessage(ans.msg, true, 'alert--success');
        if (ans.status == 0) {
            return;
        }
        mediaListing();
    });
}
$(document).ready(function() {
    $('input[name=\'taxcat_name\']').on('keyup', function(){
        if ($('input[name=\'ptt_taxcat_id\']').val != "") {
            $('input[name=\'ptt_taxcat_id\']').val(0);
        }
        
        $('input[name=\'taxcat_name\']').autocomplete({
        'classes': {
            "ui-autocomplete": "custom-ui-autocomplete"
        },
        'source': function(request, response) {
            $.ajax({
                url: fcom.makeUrl('products', 'autoCompleteTaxCategories'),
                data: {
                    keyword: request['term'],
                    fIsAjax: 1
                },
                dataType: 'json',
                type: 'post',
                success: function(json) {
                    response($.map(json, function(item) {
                        return {
                            label: item['name'],
                            value: item['name'],
                            id: item['id']
                        };
                    }));
                },
            });
        },
        select: function(event, ui) {
            $('input[name=\'ptt_taxcat_id\']').val(ui.item.id);
        }
    });
    });
    

    $('input[name=\'taxcat_name\']').on('change', function() {
        if ($(this).val() == '') {
            $('input[name=\'ptt_taxcat_id\']').val(0);
        }
    });
});
</script>
<style>
table {
    width: 100%;
}
</style>