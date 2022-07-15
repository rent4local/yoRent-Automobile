<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal layout--' . $formLayout);
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = '12';

$tbid = isset($tabId) ? $tabId : 'tabs_' . $frmType;
if ($lang_id > 0) {
    $frm->setFormTagAttribute('onsubmit', 'setupLang(this); return(false);');
    $langFld = $frm->getField('lang_id');
    $langFld->setfieldTagAttribute('onChange', "getLangForm(" . $frmType . ", this.value, '" . $tbid . "');");
} else {
    $frm->setFormTagAttribute('onsubmit', 'setup(this); return(false);');
}

$stateData = FatApp::getConfig('CONF_STATE', FatUtility::VAR_INT, 1);
$displayMap = false;

switch ($frmType) {
    case Configurations::FORM_GENERAL:
        break;
    case Configurations::FORM_MEDIA:
        break;
    
        case Configurations::FORM_PRODUCT:
            if (FatApp::getConfig('CONF_PRODUCT_GEO_LOCATION', FatUtility::VAR_INT, 0) == applicationConstants::BASED_ON_RADIUS) {
                $lFld->setFieldTagAttribute('disabled', 'disabled');
            }
    
            $fld = $frm->getField('CONF_DEFAULT_GEO_LOCATION');
            $fld->setFieldTagAttribute('class', 'defaultLocationGeoFilter');
    
            $countryFld = $frm->getField('CONF_GEO_DEFAULT_COUNTRY');
            $stateFld = $frm->getField('CONF_GEO_DEFAULT_STATE');
            
    
            if ($countryFld) {
                $countryFld->setFieldTagAttribute('id', 'geo_country_code');
                $countryFld->setFieldTagAttribute('onChange', 'getStatesByCountryCode(this.value,' . FatApp::getConfig('CONF_GEO_DEFAULT_STATE', FatUtility::VAR_STRING, 1) . ',\'#geo_state_code\', \'state_code\')');
    
                $stateFld->setFieldTagAttribute('id', 'geo_state_code');
            }
            $stateData = FatApp::getConfig('CONF_GEO_DEFAULT_STATE', FatUtility::VAR_INT, 1);
    
            $zipFld = $frm->getField('CONF_GEO_DEFAULT_ZIPCODE');
            $zipFld->setFieldTagAttribute('id', 'geo_postal_code');
    
            if (FatApp::getConfig('CONF_DEFAULT_GEO_LOCATION', FatUtility::VAR_INT, 0) != applicationConstants::YES) {
                $countryFld->setFieldTagAttribute('disabled', 'disabled');
                $stateFld->setFieldTagAttribute('disabled', 'disabled');
                $zipFld->setFieldTagAttribute('disabled', 'disabled');
            }
    
            $latFld = $frm->getField('CONF_GEO_DEFAULT_LAT');
            $latFld->setFieldTagAttribute('id', "lat");
            $lngFld = $frm->getField('CONF_GEO_DEFAULT_LNG');
            $lngFld->setFieldTagAttribute('id', "lng");
            $lngFld = $frm->getField('CONF_GEO_DEFAULT_ADDR');
            $lngFld->setFieldTagAttribute('id', "geo_city");
    
            $displayMap = true;
            break;
    case Configurations::FORM_LOCAL:
        $countryFld = $frm->getField('CONF_COUNTRY');
        if ($countryFld) {
            $countryFld->setFieldTagAttribute('id', 'user_country_id');
            $countryFld->setFieldTagAttribute('onChange', 'getCountryStates(this.value,' . FatApp::getConfig('CONF_STATE', FatUtility::VAR_INT, 1) . ',\'#user_state_id\')');

            $stateFld = $frm->getField('CONF_STATE');
            $stateFld->setFieldTagAttribute('id', 'user_state_id');
        }
        break;

    case Configurations::FORM_DISCOUNT:
        break;
    case Configurations::FORM_ORDERS:
        $addressType = FatApp::getConfig('CONF_ADDRESS_ON_ORDER_DETAIL_PRINT', FatUtility::VAR_INT, 0);
        $invfld = $frm->getField('CONF_GOV_INFO_ON_INVOICE');
        if ($addressType != 0 && !empty($invfld)) {
            $invfld->setFieldTagAttribute('disabled', 'disabled');
        }
        break;    
        
}
?>
<ul class="tabs_nav innerul">
    <?php if ($frmType == Configurations::FORM_IMPORT_EXPORT) { ?>
        <li><a href="javascript:void(0);" onclick="generalInstructions(<?php echo $frmType; ?>);"><?php echo Labels::getLabel('LBL_Instructions', $adminLangId); ?></a>
        </li>
    <?php } 
    if ($frmType != Configurations::FORM_MEDIA && $frmType != Configurations::FORM_SHARING) { ?>
        <li><a class="<?php echo ($lang_id == 0) ? 'active' : ''; ?>" href="javascript:void(0)" onClick="getForm(<?php echo $frmType; ?>, '<?php echo $tbid; ?>')"><?php echo Labels::getLabel('LBL_Basic', $adminLangId); ?></a>
        </li>
    <?php }
    if ($dispLangTab) { ?>
        <li>
            <a class="<?php echo (0 < $lang_id ? 'active' : '') ?>" href="javascript:void(0);" onClick="getLangForm(<?php echo $frmType; ?>, <?php echo FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1); ?>, '<?php echo $tbid; ?>')">
                <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
            </a>
        </li>
    <?php } ?>
</ul>
<div class="tabs_panel_wrap">
    <?php if ($frmType == Configurations::FORM_MEDIA) { 
        /* [ MEDIA INSTRUCTIONS START HERE */
        $tpl = new FatTemplate('', '');
        $tpl->set('adminLangId', $adminLangId);
        $mediaInstruction = $tpl->render(false, false, '_partial/imageUploadInstructions.php', true, true);
        /* ] */
    
        $instFld = $frm->getField('media_instruction');
        $instFld->value = $mediaInstruction;
    } ?>
    
    <?php echo $frm->getFormHtml(); ?>
    <?php if ($displayMap && !empty(FatApp::getConfig('CONF_GOOGLEMAP_API_KEY', FatUtility::VAR_STRING, ''))) { ?>
        <div id="map" style="width:900px; height:500px"></div>
    <?php } ?>
</div>
<script>
    <?php if ($displayMap && !empty(FatApp::getConfig('CONF_GOOGLEMAP_API_KEY', FatUtility::VAR_STRING, ''))) { ?>
        getStatesByCountryCode($("#geo_country_code").val(), '<?php echo FatApp::getConfig('CONF_GEO_DEFAULT_STATE', FatUtility::VAR_STRING, 1); ?>', '#geo_state_code', 'state_code');
    <?php } ?>
    $(document).ready(function () {
        <?php if (FatApp::getConfig('CONF_ALLOW_RENTAL_ORDER_CANCEL_FROM_BUYER_END', FatUtility::VAR_INT, 0) == 0) { ?>
            $('.penalty-checkbox--js').find('input[type="radio"]').attr('disabled', 'true');
        <?php } ?>

        $('input[name="CONF_ALLOW_RENTAL_ORDER_CANCEL_FROM_BUYER_END"]').on('change', function () {
            if ($(this).val() == 1) {
                $('.penalty-checkbox--js').find('input[type="radio"]').removeAttr('disabled');
            } else {
                $('.penalty-checkbox--js').find('input[type="radio"]').attr('disabled', 'true');
            }
        });
    });
</script>

<script language="javascript">
    var aspectRatio = '';
    
    $(document).ready(function () {
        /* $('.prefRatio-js').trigger('change'); */
        $(".financial-year--js").datepicker({ 
            changeYear: false, 
            dateFormat: 'MM-dd',
        }).focus(function() {
            $(".ui-datepicker-year").hide();
        });
    });

    $(".imageRatio").on('change', function() {
        aspectRatio = getRation(this);
    });

    function getRation(inputBtn) {
        let aspRatio = ''; 
        if($(inputBtn).attr('data-ratio') == <?php echo AttachedFile::RATIO_TYPE_SQUARE; ?>) {
            aspRatio = 1/1;
        } else if($(inputBtn).attr('data-ratio') == <?php echo AttachedFile::RATIO_TYPE_RECTANGULAR; ?>) {
            aspRatio = 16/9;
        }
        return aspRatio;
    }

    $(".imageUpload").on('change', function() {
        if($(this).attr('data-file_type') == <?php echo AttachedFile::FILETYPE_META_IMAGE; ?>) {
            aspectRatio = 3/2;
        } else if($(this).attr('data-file_type') == <?php echo AttachedFile::FILETYPE_WATERMARK_IMAGE; ?> || $(this).attr('data-file_type') == <?php echo AttachedFile::FILETYPE_MOBILE_LOGO; ?>) {
            aspectRatio = 16/9;
        } else if($(this).attr('data-file_type') == <?php echo AttachedFile::FILETYPE_APPLE_TOUCH_ICON; ?>  || $(this).attr('data-file_type') == <?php echo AttachedFile::FILETYPE_FIRST_PURCHASE_DISCOUNT_IMAGE; ?> || $(this).attr('data-file_type') == <?php echo AttachedFile::FILETYPE_FAVICON; ?>) {
            aspectRatio = 1/1;
        } else if($(this).attr('data-file_type') == <?php echo AttachedFile::FILETYPE_SOCIAL_FEED_IMAGE; ?>) {
            aspectRatio = 2/3;
        } else if ($(this).attr('data-file_type') == <?php echo AttachedFile::FILETYPE_INVOICE_LOGO; ?>) {
            aspectRatio = 2/1;
        }
    });

    $(document).on('change', '.prefRatio-js', function () {
        aspectRatio = ''
        var inputElement = $(this).parents('.list-inline').next('input');
        var selectedVal = $(this).val();
        if (selectedVal == <?php echo AttachedFile::RATIO_TYPE_SQUARE; ?>) {
            inputElement.attr('data-min_width', 150);
            inputElement.attr('data-min_height', 150);
            inputElement.attr('data-ratio', <?php echo AttachedFile::RATIO_TYPE_SQUARE; ?>);
            aspectRatio = 1/1; 
        } else if(selectedVal == <?php echo AttachedFile::RATIO_TYPE_RECTANGULAR?>) {
            inputElement.attr('data-min_width', 150);
            inputElement.attr('data-min_height', 85);
            inputElement.attr('data-ratio', <?php echo AttachedFile::RATIO_TYPE_RECTANGULAR; ?>);
            aspectRatio = 16/9;
        } else {
            inputElement.attr('data-min_width', 200);
            inputElement.attr('data-min_height', 100);
            inputElement.attr('data-ratio', <?php echo AttachedFile::RATIO_TYPE_CUSTOM; ?>);
            aspectRatio = '';
        }
    });

    $(document).on('change', '.geoLocation', function () {
        var geolocVal = $(this).val();

        $('.listingFilter').removeAttr('disabled');
        if (geolocVal == <?php echo applicationConstants::BASED_ON_RADIUS; ?>) {
            $('.listingFilter').attr('disabled', 'disabled');
            $('input[name="CONF_RADIUS_DISTANCE_IN_MILES"]').prop('disabled', false); 
        } else {
            $('input[name="CONF_RADIUS_DISTANCE_IN_MILES"]').prop('disabled', true); 
        }

        if (geolocVal == <?php echo applicationConstants::BASED_ON_DELIVERY_LOCATION; ?>) {
            $('.listingFilter').each(function () {
                if ($(this).val() == <?php echo applicationConstants::LOCATION_ZIP; ?>) {
                    $(this).attr('disabled', 'disabled');
                }
            });
        }
    });

    $(document).on('change', '.defaultLocationGeoFilter', function() {
        if ($(this).val() == 1) {
            $('select[name="CONF_GEO_DEFAULT_COUNTRY"]').prop('disabled', false); 
            $('select[name="CONF_GEO_DEFAULT_STATE"]').prop('disabled', false); 
            $('input[name="CONF_GEO_DEFAULT_ZIPCODE"]').prop('disabled', false); 
        } else {
            $('select[name="CONF_GEO_DEFAULT_COUNTRY"]').prop('disabled', true); 
            $('select[name="CONF_GEO_DEFAULT_STATE"]').prop('disabled', true); 
            $('input[name="CONF_GEO_DEFAULT_ZIPCODE"]').prop('disabled', true);
        }
    });

    $(document).on('change', '.address-type--js input[type="radio"]', function() {
        if ($(this).val() == 0) {
            $('.govt-info--js').removeAttr('disabled');
        } else {
           $('.govt-info--js').attr('disabled', 'disabled');
        }
    });
    
    <?php if ($displayMap && !empty(FatApp::getConfig('CONF_GOOGLEMAP_API_KEY', FatUtility::VAR_STRING, ''))) { ?>
        $(document).ready(function() {
            var lat = $('#lat').val();
            var lng = $('#lng').val();
            initMap(lat, lng);     
        });
    <?php } else { ?>
        getCountryStates($("#user_country_id").val(), '<?php echo $stateData; ?>', '#user_state_id');
    <?php } ?>
    
    $('select[name="CONF_DEFAULT_SITE_LANG"]').on('change', function(e){
        if (!confirm(langLbl.confirmSiteLangChange)) { 
           $('select[name="CONF_DEFAULT_SITE_LANG"]').val(<?php echo FatApp::getConfig('CONF_DEFAULT_SITE_LANG', FatUtility::VAR_INT, 1);?>);
        } 
    });
</script>
<?php if ($frmType == Configurations::FORM_ORDERS) { ?>
<style>
.form_horizontal .field-set .caption-wraper {width: 50%;}
</style>
<?php } ?>
<?php if($frmType == Configurations::FORM_GENERAL) { ?>
    <script language="javascript">
        $(document).ready(function(){
            stylePhoneNumberFld("input[name='CONF_SITE_PHONE']");
            stylePhoneNumberFld("input[name='CONF_SITE_FAX']", false, true);
        });	
    </script>
    <?php
    if (isset($record['CONF_SITE_PHONE_ISO']) && !empty($record['CONF_SITE_PHONE_ISO'])) { ?>
        <script>
            langLbl.defaultCountryCode = '<?php echo $record['CONF_SITE_PHONE_ISO']; ?>';
        </script>
    <?php } 
    if (isset($record['CONF_SITE_FAX_ISO']) && !empty($record['CONF_SITE_FAX_ISO'])) { ?>
        <script>
            langLbl.defaultCountryCode2 = '<?php echo $record['CONF_SITE_FAX_ISO']; ?>';
        </script>
    <?php } ?>
<?php } ?>