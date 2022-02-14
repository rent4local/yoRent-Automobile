<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form');
$frm->setFormTagAttribute('onsubmit', 'setupVerificationFlds(this); return(false);');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;

?>
<section class="section">
	<div class="sectionhead">
		<h4><?php echo Labels::getLabel('LBL_Verification_Field_Setup', $adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
		<div class="tabs_nav_container responsive flat">
			<div class="tabs_panel_wrap">
				<div class="tabs_panel">
					<div class="row justify-content-center">
						<div class="col-md-12">
							<?php echo $frm->getFormTag(); ?>
							<div class="row">
								<div class="col-md-6">
									<div class="field-set">
										<div class="caption-wraper">
											<label class="field_label">
												<?php $fld = $frm->getField('vflds_name[' . $siteDefaultLangId . ']');
												echo $fld->getCaption(); ?>
												<span class="spn_must_field">*</span></label>
										</div>
										<div class="field-wraper">
											<div class="field_cover">
												<?php echo $frm->getFieldHtml('vflds_name[' . $siteDefaultLangId . ']'); ?>
											</div>
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="field-set">
										<div class="caption-wraper">
											<label class="field_label">
												<?php $fld = $frm->getField('vflds_type');
												echo $fld->getCaption(); ?>
												<span class="spn_must_field">*</span></label>
										</div>
										<div class="field-wraper">
											<div class="field_cover">
												<?php echo $frm->getFieldHtml('vflds_type'); ?>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="field-set">
										<div class="caption-wraper">
											<label class="field_label">
												<?php $fld = $frm->getField('vflds_required');
												echo $fld->getCaption(); ?>
												<span class="spn_must_field">*</span></label>
										</div>
										<div class="field-wraper">
											<div class="field_cover">
												<?php echo $frm->getFieldHtml('vflds_required'); ?>
											</div>
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="field-set">
										<div class="caption-wraper">
											<label class="field_label">
												<?php $fld = $frm->getField('vflds_active');
												echo $fld->getCaption(); ?>
												<span class="spn_must_field">*</span></label>
										</div>
										<div class="field-wraper">
											<div class="field_cover">
												<?php echo $frm->getFieldHtml('vflds_active'); ?>
											</div>
										</div>
									</div>
								</div>
							</div>

							<?php if (!empty($otherLangData)) {
								foreach ($otherLangData as $langId => $data) { ?>
									<div class="accordians_container accordians_container-categories" defaultLang="<?php echo $siteDefaultLangId; ?>" language="<?php echo $langId; ?>" id="accordion-language_<?php echo $langId; ?>" onClick="translateData(this)">
										<div class="accordian_panel">
											<span class="accordian_title accordianhead accordian_title" id="collapse_<?php echo $langId; ?>">
												<?php echo $data . " ";
												echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
											</span>
											<div class="accordian_body accordiancontent p-0" style="display: none;">
												<div class="row">
													<div class="col-md-6">
														<div class="field-set">
															<div class="caption-wraper">
																<label class="field_label">
																	<?php $fld = $frm->getField('vflds_name[' . $langId . ']');
																	echo $fld->getCaption(); ?>
																</label>
															</div>
															<div class="field-wraper">
																<div class="field_cover">
																	<?php echo $frm->getFieldHtml('vflds_name[' . $langId . ']'); ?>
																</div>
															</div>
														</div>
													</div>
												</div>

											</div>
										</div>
									</div>
							<?php }
							} ?>
							<div class="row">
								<div class="col-md-12">
									<div class="field-set d-flex align-items-center">
										<div class="field-wraper w-auto">
											<div class="field_cover">
												<?php echo $frm->getFieldHtml('btn_submit'); ?>
											</div>
										</div>
									</div>
								</div>
							</div>
							<?php echo $frm->getFieldHtml('vflds_id'); ?>
							</form>
							<?php echo $frm->getExternalJS(); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
