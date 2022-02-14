<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$frm->setFormTagAttribute('class', 'form product-form--js_'. $type );
$frm->setFormTagAttribute('onsubmit', 'setupProfileProduct(this, '. $type .'); return(false);');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 6;


$proFld = $frm->getField("product_name");
$proFld->developerTags['col'] = 8;
$proFld->developerTags['noCaptionTag'] = true;
$proFld->setFieldTagAttribute('id', 'product_name--js');
$proFld->addFieldTagAttribute('placeholder', Labels::getLabel('LBL_Search_Product', $siteLangId));
if ($type == SellerProduct::PRODUCT_TYPE_ADDON) {
    $proFld->setFieldTagAttribute('id', 'service_name--js');
    $proFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Search_Addon', $siteLangId));
}


$submitBtnFld = $frm->getField('btn_submit');
$submitBtnFld->setFieldTagAttribute('class', 'btn btn-brand');
$submitBtnFld->setWrapperAttribute('class', 'col-lg-2');
$submitBtnFld->developerTags['col'] = 4;
$submitBtnFld->developerTags['noCaptionTag'] = true;

$proFld->htmlAfterField = "<span class='form-text text-muted text-danger'>" . Labels::getLabel("LBL_Product/Services_will_automatically_remove_from_other_profile", $siteLangId) . "</span>";


?>
<div class="card-header">
    <h5 class="card-title"><?php 
        if ($type == SellerProduct::PRODUCT_TYPE_ADDON) {
            echo Labels::getLabel('LBL_Addons', $siteLangId);
        } else {
            echo Labels::getLabel('LBL_Products', $siteLangId);
        }
        ?>
    </h5>
</div>
<div class="card-body">
    <div class="row">
        <div class="col-md-12">
            <?php echo $frm->getFormHtml(); ?>
            </form>
        </div>
    </div>
    <div id="<?php echo ($type == SellerProduct::PRODUCT_TYPE_ADDON) ? 'services-listing--js' : 'product-listing--js'; ?>"></div>
</div>

<style>
.select2  {width : 100% !important;}    
</style>
<?php 
$autoCompleteAction = 'autoComplete';
$fldIdentifier = '#product_name--js';
if ($type == SellerProduct::PRODUCT_TYPE_ADDON) {  
    $autoCompleteAction = 'servicesAutoComplete';
    $fldIdentifier = '#service_name--js';
} 
?>

<script>
$(document).ready(function(){   
    $("<?php echo $fldIdentifier; ?>").select2({
        closeOnSelect: true,
        dir: langLbl.layoutDirection,
        allowClear: true,
        placeholder: $("<?php echo $fldIdentifier; ?>").attr('placeholder'),
        ajax: {
            url: fcom.makeUrl('LateCharges', '<?php echo $autoCompleteAction;?>'),
            dataType: 'json',
            delay: 250,
            method: 'post',
            data: function (params) {
                return {
                    keyword: params.term, // search term
                    page: params.page
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                return {
                    results: data.products,
                    pagination: {
                        more: params.page < data.pageCount
                    }
                };
            },
            cache: true
        },
        minimumInputLength: 0,
        templateResult: function (result)
        {
            return result.name;
        },
        templateSelection: function (result)
        {
            return result.name || result.text;
        }
    }).on('select2:selecting', function (e)
    {
        var parentForm = $(this).closest('form').attr('id');
        var item = e.params.args.data;
        $("#" + parentForm + " input[name='lcptp_product_id']").val(item.id);
    }).on('select2:unselecting', function (e)
    {
        var parentForm = $(this).closest('form').attr('id');
        $("#" + parentForm + " input[name='lcptp_product_id']").val('');
    });
});
</script>
