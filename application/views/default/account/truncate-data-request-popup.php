<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="modal-dialog modal-dialog-centered" role="document" id="truncate-list">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><?php echo Labels::getLabel('LBL_Truncate_Request', $siteLangId); ?></h5>

            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form class="form form--horizontal">
            <div class="modal-body">
                <div class="cols--group">
                    <div class="box__body">
                        <div class="form__subcontent">
                            <div class=""><?php echo Labels::getLabel('LBL_Truncate_request_approval_will_delete_all_your_data._Truncate_anyway?', $siteLangId); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <input class="btn btn-brand" type="button" name="btn_submit" onclick="sendTruncateRequest()" value="<?php echo Labels::getLabel('LBL_Yes', $siteLangId); ?>">
                <input class="btn btn-outline-brand ml-2" onclick="cancelTruncateRequest()" type="button" name="btn_cancel" value="<?php echo Labels::getLabel('LBL_Cancel', $siteLangId); ?>">
            </div>
        </form>
    </div>
</div>