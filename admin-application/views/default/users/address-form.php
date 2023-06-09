<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$addressFrm->setFormTagAttribute('id', 'addressFrm');
$addressFrm->setFormTagAttribute('class','web_form form_horizontal');

$addressFrm->setFormTagAttribute('onsubmit', 'setupAddress(this); return(false);');

$addressFrm->developerTags['colClassPrefix'] = 'col-md-';
$addressFrm->developerTags['fld_default_col'] = 12;

$countryFld = $addressFrm->getField('addr_country_id');
$countryFld->setFieldTagAttribute('id','addr_country_id');
$countryFld->setFieldTagAttribute('onChange','getCountryStates(this.value,'.$stateId.',\'#addr_state_id\')');

$stateFld = $addressFrm->getField('addr_state_id');
$stateFld->setFieldTagAttribute('id','addr_state_id');

?>

<section class="section">
	<div class="sectionhead">
		<h4><?php echo Labels::getLabel('LBL_User_Addresses',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">      
	  <div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a href="javascript:void(0)" onclick="userForm(<?php echo $user_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<li><a href="javascript:void(0)" onclick="addBankInfoForm(<?php echo $user_id ?>);"><?php echo Labels::getLabel('LBL_Bank_Info',$adminLangId); ?></a></li>
			<li><a class="active" href="javascript:void(0)" onclick="addUserAddress(<?php echo $user_id ?>);"><?php echo Labels::getLabel('LBL_Addresses',$adminLangId); ?></a></li>							
		</ul>
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $addressFrm->getFormHtml(); ?>
			</div>
		</div>						
	</div>
	</div>						
</section>

<script language="javascript">
$(document).ready(function(){
	getCountryStates($( "#addr_country_id" ).val(),<?php echo $stateId ;?>,'#addr_state_id');
	stylePhoneNumberFld("input[name='addr_phone']");
});	
</script>

<?php
if (isset($countryIso) && !empty($countryIso)) { ?>
    <script>
        langLbl.defaultCountryCode = '<?php echo $countryIso; ?>';
    </script>
<?php } ?>