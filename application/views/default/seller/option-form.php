<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="modal-dialog modal-dialog-centered modal-lg" role="document" id="option-form">
  <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title"><?php echo Labels::getLabel('LBL_OPTION_SETUP', $langId); ?></h5>

      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class="modal-body">
      <!-- <div class="pop-up-title">
        <?php echo Labels::getLabel('LBL_OPTION_SETUP', $langId); ?>
      </div> -->
      <div id="loadForm"><?php echo Labels::getLabel('LBL_LOADING..', $langId); ?></div>
      <?php if ($option_id > 0) { ?>
        <div class="gap"></div>
        <div class="row">
          <div class="col-lg-12 col-md-12 col-sm-12 col-xm-12" id="showHideContainer">
            <section class="">
              <div class="section-head">
                <div class="section__heading">
                  <h4><?php echo Labels::getLabel('LBL_Options_Listing', $langId); ?></h4>
                </div>
                <div class="section__action"> <a href="javascript:void(0)" class="btn btn-brand btn-sm ripplelink" onClick="optionValueForm(<?php echo $option_id; ?>,0)" ;><?php echo Labels::getLabel('LBL_ADD_NEW', $langId); ?></a> </div>
              </div>
              <div class="sectionbody">
                <div class="tablewrap">
                  <div id="optionValueListing"></div>
                </div>
              </div>
            </section>
          </div>
        </div>
      <?php } ?>

    </div>
  </div>
</div>