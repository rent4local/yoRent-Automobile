<?php
if (isset($postedData) && !empty($postedData)) {
    $searchForm->fill($postedData);
}    
    
$searchForm->setFormTagAttribute('onSubmit', 'submitSiteSearch(this, 12); return(false);');
$keywordFld = $searchForm->getField('keyword');
$keywordFld->addFieldTagAttribute('placeholder', Labels::getLabel('LBL_Keyword_Search', $siteLangId));

$geoAddress = '';
if ((!isset($_COOKIE['_ykGeoLat']) || !isset($_COOKIE['_ykGeoLng']) || !isset($_COOKIE['_ykGeoCountryCode'])) && FatApp::getConfig('CONF_DEFAULT_GEO_LOCATION', FatUtility::VAR_INT, 0)) {
    $geoAddress = FatApp::getConfig('CONF_GEO_DEFAULT_ADDR', FatUtility::VAR_STRING, '');
    if (empty($address)) {
        $address = FatApp::getConfig('CONF_GEO_DEFAULT_ZIPCODE', FatUtility::VAR_INT, 0) . '-' . FatApp::getConfig('CONF_GEO_DEFAULT_STATE', FatUtility::VAR_STRING, '');
    }
}

$locFld = $searchForm->getField('location');
$locFld->addFieldTagAttribute('placeholder', Labels::getLabel('LBL_Location_Search', $siteLangId)); 
$idPostfix = (isset($isHome) && $isHome) ? 1 : 2;
$locFld->addFieldTagAttribute('id', 'ga-autoComplete-header-'. $idPostfix);
$locFld->setFieldTagAttribute('onKeyup', "googleAddressAutocomplete('ga-autoComplete-header-". $idPostfix ."', 'formatted_address', true, 'googleSelectedAddress', 0)");
$locFld->value = isset($_COOKIE["_ykGeoAddress"]) ? $_COOKIE["_ykGeoAddress"] : $geoAddress;

$dateFld = $searchForm->getField('rentaldates');
$dateFld->addFieldTagAttribute('data-section','search-section-auto');
$dateFld->addFieldTagAttribute('placeholder', Labels::getLabel('LBL_Add_Dates', $siteLangId));
if (isset($postedData['rentalstart']) && !empty($postedData['rentalstart'])) {
    $dateFld->addFieldTagAttribute('id', date('d/m/Y',strtotime($postedData['rentalstart'])) . ' - ' . date('d/m/Y',strtotime($postedData['rentalend'])));
    $dateFld->value = $postedData['rentalstart'] . ' to '. $postedData['rentalend'];
}

if(isset($headerSearch)){
echo "<div id='HDR-SEARCH' class='main-search-bar'>";
}
?>
	<?php echo $searchForm->getFormTag(); ?>
	<div class="homesearch">
		<?php
		$searchForm->setFormTagAttribute('onSubmit', 'submitSiteSearch(this, 12); return(false);');
		$keywordFld = $searchForm->getField('keyword');
		$keywordFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Keyword_Search', $siteLangId));

		$locFld = $searchForm->getField('location');
		$locFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Location_Search', $siteLangId));
		$locFld->setFieldTagAttribute('class', 'location_input location-selected');
		/* $locFld->setFieldTagAttribute('id', 'ga-autoComplete'); */

		$locFld = $searchForm->getField('rentaldates');
		$locFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Add_Dates', $siteLangId));

		$locFld = $searchForm->getField('searchButton');
		$locFld->setFieldTagAttribute('class', "btn btn-brand btn-round");
		$locFld->value = '<button class="btn btn-brand btn-round">' . Labels::getLabel('LBL_Search', $siteLangId) . '</button>';

		?>
		<div class="homesearch__search">
			<div class="field-icon">
				<i class="icn">
					<svg class="svg" width="18" height="18">
						<use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#maginifier" href="<?php echo CONF_WEBROOT_URL; ?>images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#maginifier"></use>
					</svg>
				</i>
			</div>
			<div class="field-set">
				<div class="caption-wraper">
					<label class="field_label"><?php echo $searchForm->getField('keyword')->getCaption(); ?><span class="spn_must_field">*</span></label>
				</div>
				<div class="field-wraper">
					<div class="field_cover">
						<?php echo $searchForm->getFieldHtml('keyword'); ?>
					</div>
				</div>
			</div>
		</div>

		<div class="homesearch__search">
			<div class="field-icon">
				<i class="icn">
					<svg class="svg" width="20" height="20">
						<use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#location" href="<?php echo CONF_WEBROOT_URL; ?>images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#location"></use>
					</svg>
				</i>
			</div>
			<div class="field-set">
				<div class="caption-wraper">
					<label class="field_label"><?php echo $searchForm->getField('location')->getCaption(); ?><span class="spn_must_field">*</span></label>
				</div>
				<div class="field-wraper">
					<div class="field_cover">
						<?php echo $searchForm->getFieldHtml('location'); ?>
					</div>
				</div>
			</div>
		</div>
		<div class="homesearch__date">
			<div class="field-icon">
				<i class="icn">
					<svg class="svg" width="20" height="20">
						<use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#calender" href="<?php echo CONF_WEBROOT_URL; ?>images/<?php echo ACTIVE_THEME; ?>/retina/sprite.svg#calender"></use>
					</svg>
				</i>
			</div>
			<div class="field-set">
				<div class="caption-wraper">
					<label class="field_label"><?php echo $searchForm->getField('rentaldates')->getCaption(); ?></label>
				</div>
				<div class="field-wraper search-section-auto date-selector">
					<div class="field_cover">
						<?php echo $searchForm->getFieldHtml('rentaldates'); ?>
						<?php echo $searchForm->getFieldHtml('rentalstart'); ?>
						<?php echo $searchForm->getFieldHtml('rentalend'); ?>
					</div>
				</div>
			</div>
		</div>
		<div class="homesearch__btn">
			<?php echo $searchForm->getFieldHtml('searchButton'); ?>
		</div>
	</div>
	</form>
<?php if(isset($headerSearch)){
echo "</div>";
} ?>