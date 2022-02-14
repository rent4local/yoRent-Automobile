<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$this->includeTemplate('_partial/seller/sellerDashboardNavigation.php');
$frm->setFormTagAttribute('class', 'form');
$frm->setFormTagAttribute('onsubmit', 'setupProfile(this); return(false);');


$submitBtnFld = $frm->getField('btn_submit');
$submitBtnFld->setFieldTagAttribute('class', 'btn btn-brand');
$submitBtnFld->setWrapperAttribute('class', 'col-lg-2');
$submitBtnFld->developerTags['col'] = 2;
$submitBtnFld->developerTags['noCaptionTag'] = true;

?>
<main id="main-area" class="main"   >
    <div class="content-wrapper content-space">
        <div class="content-header row">
            <div class="col">
                <h5 class="content-header-title"><?php echo Labels::getLabel('LBL_Late_Charges_Profiles', $siteLangId); ?>
                </h5>
            </div>
            <div class="col-auto">
                <div class="content-header-right">
                    <a href="<?php echo UrlHelper::generateUrl('lateCharges'); ?>" class="btn btn-outline-brand btn-sm"><?php echo Labels::getLabel('LBL_back', $siteLangId); ?></a>
                </div>
            </div>
        </div>
        <div class="content-body">
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body pt-4 pl-4 pr-4 pb-4">
                            <?php echo $frm->getFormTag();
                                        $pNameFld = $frm->getField('lcp_identifier');
                                        $pNameFld->htmlAfterField = "<span class='form-text text-muted'>" . Labels::getLabel("LBL_Customers_will_not_see_this.", $siteLangId) . "</span>";

                                        $pNameFld->addFieldTagAttribute('placeholder', Labels::getLabel('LBL_Profile_Name', $siteLangId));
                                        $pNameFld->addFieldTagAttribute('class', 'form-control');

                                        $lcpAmount = $frm->getField('lcp_amount');
                                        $lcpAmount->addFieldTagAttribute('placeholder', Labels::getLabel('LBL_Amount', $siteLangId));
                                        ?>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group mb-0">
                                                    <?php
                                                    if (!empty($profileData) && $profileData['lcp_is_default'] == 1) {
                                                        $pNameFld->addFieldTagAttribute('readonly', 'true');
                                                        $pNameFld->addFieldTagAttribute('disabled', 'true');
                                                    }
                                                    echo $frm->getFieldHtml('lcp_identifier'); ?>
                                                </div>
                                            </div>
											<div  class="col-md-3">
												<div class="form-group mb-0">
													<?php echo $frm->getFieldHtml('lcp_amount_type'); ?>
												</div>
											</div>
											<div  class="col-md-3">
												<div class="form-group mb-0">
													<?php echo $frm->getFieldHtml('lcp_amount'); ?>
												</div>
											</div>
											
                                            <div class="col-md-3">
                                                <div class="form-group mb-0">
                                                    <?php
                                                    echo $frm->getFieldHtml('lcp_id');
                                                    echo $frm->getFieldHtml('lcp_user_id');

                                                    //if (empty($profileData) || ((isset($profileData['lcp_is_default']) && $profileData['lcp_is_default'] != 1))) {
                                                        echo $frm->getFieldHtml('btn_submit');
                                                    //}
                                                    ?>
                                                </div>
                                            </div>
											
											
                                        </div>
                                        </form>
                                        <?php echo $frm->getExternalJs(); ?>

                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card" id="product-section--js"> </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card" id="services-section--js"> </div>
                </div>
            </div>
            
        </div>
    </div>
</main>