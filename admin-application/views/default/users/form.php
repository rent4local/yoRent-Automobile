<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$dobFld = $frmUser->getField('user_dob');
$dobFld->setFieldTagAttribute('class','user_dob_js');

if ($user_id > 0) {
    $fld_credential_username = $frmUser->getField('credential_username');
    $fld_credential_username->setFieldTagAttribute('disabled', 'disabled');

    $user_email = $frmUser->getField('credential_email');
    if(!empty($data['credential_email'])) {
        $user_email->setFieldTagAttribute('disabled', 'disabled');
    }
    $user_email->setFieldTagAttribute('id', 'user-email');
    $user_email->setFieldTagAttribute('data-value', $data['credential_email']);
    $user_email->setFieldTagAttribute('data-encrypted-value', CommonHelper::displayEncryptedEmail($data['credential_email']));
    /* $user_email->htmlAfterField = '<span toggle="#user-email" onClick ="toggleEncryptedFields(this)" class="fa js-toggle-data fa-eye"></span>'; */
    if (!empty($data['user_dob']) && $data['user_dob'] != '0000-00-00') {
        $dobFld->setFieldTagAttribute('id', 'user-dob');
        $dobFld->setFieldTagAttribute('data-value', $data['user_dob']);
        /* $dobFld->setFieldTagAttribute('data-encrypted-value', CommonHelper::displayEncryptedDob($data['user_dob']));
        $dobFld->htmlAfterField = '<span toggle="#user-dob" onClick ="toggleEncryptedFields(this,1)" class="fa js-toggle-data fa-eye"></span>'; */
    }
    if (!empty($data['user_phone'])) {
        $phoneFld = $frmUser->getField('user_phone');
        $phoneFld->setFieldTagAttribute('id', 'user-phone');
        $phoneFld->setFieldTagAttribute('data-value', $data['user_phone']);
        /* $phoneFld->setFieldTagAttribute('data-encrypted-value', CommonHelper::displayEncryptedFieldData($data['user_phone']));
        $phoneFld->htmlAfterField = '<span toggle="#user-phone" onClick ="toggleEncryptedFields(this, 1, 1)" class="fa js-toggle-data fa-eye"></span>'; */
    }
}

$frmUser->developerTags['colClassPrefix'] = 'col-md-';
$frmUser->developerTags['fld_default_col'] = 12;	

$frmUser->setFormTagAttribute('class', 'web_form form_horizontal');
$frmUser->setFormTagAttribute('onsubmit', 'setupUsers(this); return(false);');

$countryFld = $frmUser->getField('user_country_id');
$countryFld->setFieldTagAttribute('id','user_country_id');
$countryFld->setFieldTagAttribute('onChange','getCountryStates(this.value,'.$stateId.',\'#user_state_id\')');

$stateFld = $frmUser->getField('user_state_id');
$stateFld->setFieldTagAttribute('id','user_state_id');

?>
<section class="section">
	<div class="sectionhead">
		<h4><?php echo Labels::getLabel('LBL_User_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">      
		<div class="tabs_nav_container responsive flat">

		<?php if($user_id > 0){ ?>
			<ul class="tabs_nav">
				<li><a class="active" href="javascript:void(0)" onclick="userForm(<?php echo $user_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
				<?php if($userParent == 0) { ?>
					<li><a href="javascript:void(0)" onclick="addBankInfoForm(<?php echo $user_id ?>);"><?php echo Labels::getLabel('LBL_Bank_Info',$adminLangId); ?></a></li>
					<li><a href="javascript:void(0)" onclick="addUserAddress(<?php echo $user_id ?>);"><?php echo Labels::getLabel('LBL_Addresses',$adminLangId); ?></a></li>
				<?php }?>							
			</ul>
			<?php }?>
			
			<div class="tabs_panel_wrap">
				<div class="tabs_panel">
					<?php echo $frmUser->getFormHtml(); ?>
				</div>
			</div>						
		</div>
	</div>						
</section>	
<script language="javascript">
	$(document).ready(function(){
		getCountryStates($( "#user_country_id" ).val(),<?php echo $stateId ;?>,'#user_state_id');
		$('.user_dob_js').datepicker('option', {maxDate: new Date()});
        stylePhoneNumberFld();
	});	
</script>
<style>
.iti--separate-dial-code {width: 100%}
.phone-js {padding-left : 83px !important;}    
</style>

<?php
if (isset($countryIso) && !empty($countryIso)) { ?>
    <script>
        langLbl.defaultCountryCode = '<?php echo $countryIso; ?>';
    </script>
<?php } ?>