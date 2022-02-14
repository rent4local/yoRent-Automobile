<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->setFormTagAttribute('id', 'productsShippingForm');
$frm->setFormTagAttribute('onsubmit', 'updateStatus(this); return(false);');
$frm->setValidatorJsObjectName('productsShippingFormValidator');

$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;
?>
<section class="section">
	<div class="sectionhead">
		<h4><?php echo Labels::getLabel('LBL_Update_Shipping_Profile',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">      
	  <div class="tabs_nav_container responsive flat">
		<div class="tabs_panel_wrap">
			<div class="tabs_panel">
				<?php echo $frm->getFormHtml(); ?>
			</div>
		</div>						
	</div>
	</div>						
</section>
