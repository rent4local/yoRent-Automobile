<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$this->includeTemplate('_partial/seller/sellerDashboardNavigation.php'); ?>
<main id="main-area" class="main"   >
    <div class="content-wrapper content-space">
        <div class="content-header row">
            <div class="col">
                <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Attach_Verification_Fields', $siteLangId); ?><i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="<?php echo Labels::getLabel('LBL_Add_fields_to_the_product_which_are_required_for_renting_out_an_item.', $siteLangId); ?>"></i></h2>
            </div>
            <div class="col-auto">
                <div class="btn-group">
                    <a class="btn btn-outline-brand btn-sm" title="<?php echo Labels::getLabel('LBL_View_Verification_Fields', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('AttachVerificationFields', 'verificationFieldsList');?>"><?php echo Labels::getLabel('LBL_View_Verification_Fields', $siteLangId); ?></a>
                </div>
            </div>
        </div>
        <div class="content-body">
			<?php if($canEdit){ ?>
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <?php $verFldFrm->setFormTagAttribute('onsubmit', 'setUpVerificationFlds(this); return(false);');
                            $verFldFrm->setFormTagAttribute('class', 'form form--horizontal');
                            $prodFld = $verFldFrm->getField('product_name');
                            $prodFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Search_Product', $siteLangId));

                            $attchFld = $verFldFrm->getField('verification_fields');
                            $attchFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Attach_Verification_Fields', $siteLangId));

                            $submitBtnFld = $verFldFrm->getField('btn_submit');
                            $submitBtnFld->setFieldTagAttribute('class', 'btn btn-brand btn-block '); ?>
                            <?php echo $verFldFrm->getFormTag(); ?>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="field-set">
                                        <div class="field-wraper">
                                            <div class="field_cover">
                                                <?php echo $verFldFrm->getFieldHTML('product_name');?>
                                                <div class="list-tag-wrapper scroll scroll-y">
                                                    <ul class="list-tags" id="products_flds"></ul></div>                           
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <div class="field-set">
                                        <div class="field-wraper">
                                            <div class="field_cover custom-tagify">
                                                <?php echo $verFldFrm->getFieldHTML('verification_fields');?>
                                                <div class="list-tag-wrapper scroll scroll-y">
                                                    <ul class="list-tags" id="verification-flds"></ul></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="field-set">
                                        <div class="field-wraper">
                                            <div class="field_cover">
                                                <?php echo $verFldFrm->getFieldHTML('btn_submit');?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php echo $verFldFrm->getFieldHTML('product_id'); ?>
                        </form>
                        <?php echo $verFldFrm->getExternalJS();?>
                        </div>
                    </div>
                </div>
            </div>
			<?php }?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div id="listing">
                                <?php echo Labels::getLabel('LBL_Loading..', $siteLangId); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
