<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$frmSrch->setFormTagAttribute('onSubmit', 'searchVerificationFlds(this); return false;');
$frmSrch->setFormTagAttribute('class', 'form ');
$frmSrch->developerTags['colClassPrefix'] = 'col-md-';

$keywordFld = $frmSrch->getField('keyword');
$keywordFld->developerTags['col'] = 8;
$keywordFld->developerTags['noCaptionTag'] = true;

$submitBtnFld = $frmSrch->getField('btn_submit');
$submitBtnFld->setFieldTagAttribute('class', 'btn-block');
$submitBtnFld->setWrapperAttribute('class', 'col-6');
$submitBtnFld->developerTags['col'] = 2;
$submitBtnFld->developerTags['noCaptionTag'] = true;

$cancelBtnFld = $frmSrch->getField('btn_clear');
$cancelBtnFld->setFieldTagAttribute('class', 'btn-block');
$cancelBtnFld->setWrapperAttribute('class', 'col-6');
$cancelBtnFld->developerTags['col'] = 2;
$cancelBtnFld->developerTags['noCaptionTag'] = true;
?>
<?php $this->includeTemplate('_partial/seller/sellerDashboardNavigation.php'); ?>
<main id="main-area" class="main" role="main">
    <div class="content-wrapper content-space">
        <div class="content-header row">
            <div class="col">
                <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Verification_Fields_List', $siteLangId); ?></h2>
            </div>
            <div class="col-auto">
                <div class="btn-group">
                    <a class="btn btn-outline-brand btn-sm" title="<?php echo Labels::getLabel('LBL_Back', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('AttachVerificationFields', 'index');?>"><?php echo Labels::getLabel('LBL_Back', $siteLangId); ?></a>
                </div>
            </div>
        </div>
        <div class="content-body">
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                             
                                <?php
                                $submitFld = $frmSrch->getField('btn_submit');
                                $submitFld->setFieldTagAttribute('class', 'btn btn-brand btn-block ');

                                $fldClear = $frmSrch->getField('btn_clear');
                                $fldClear->setFieldTagAttribute('class', 'btn btn-outline-brand btn-block');
                                echo $frmSrch->getFormHtml();
                                ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div id="verificationListing"></div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
$(document).ready(function(){
	searchVerificationFlds(document.frmSearch);
});
(function() {
	var dv = '#verificationListing';
	
	searchVerificationFlds = function(frm){
		var data = fcom.frmData(frm);
		$(dv).html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('AttachVerificationFields','verificationFldSearchListing'), data, function(res){
			$(dv).html(res);
		}); 
	};
	
	goToSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmSrchPaging;		
		$(frm.page).val(page);
		searchVerificationFlds(frm);
	}
	
	clearSearch = function(){
		document.frmSearch.reset();
		searchVerificationFlds(document.frmSearch);
	};
})();
</script>