<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="step_section">
    <div class="step_head">
        <h5 class="step_title"><?php echo Labels::getLabel('LBL_Add_Digital_Sign_in_Below_Box', $siteLangId); ?></h5>
        <span class="text-uppercase"><?php echo Labels::getLabel('LBL_OR', $siteLangId); ?></span>
        <div class="text-right">
            <a class="link" href="javascript:void(0);" onClick="signatureForm();"><span><?php echo Labels::getLabel('LBL_Upload_Signature_File', $siteLangId); ?></span></a>
        </div>
    </div>
    <div class="step_body">
        <div id="signature"></div>
    </div>
</div>
<script>
var controllerName = '<?php echo (isset($updateController)) ? $updateController : "Signature"?>';
var $sigdiv = $("#signature").addClass("signature").jSignature({
    'UndoButton': true
});

/* Add Signature in the database */
function saveImage() {
    if ($sigdiv.jSignature('getData', 'native').length == 0) {
        $.mbsmessage.close();
        $.mbsmessage("<?php echo Labels::getLabel('LBL_Please_Add_Your_Digital_Signature_To_Proceed', $siteLangId); ?>", false, 'alert--danger');    
        return false;
    }
    var data = {
        'imgData': $sigdiv.jSignature('getData', 'image'),
        'record_id' : '<?php echo (isset($recordId)) ? $recordId : ""?>'
    }
    fcom.ajax(fcom.makeUrl(controllerName, 'store'), data, function(res) {
        var ans = $.parseJSON(res);
        if (ans.status == 1) {
            return true;
        } else {
            $.systemMessage(ans.msg, 'alert--danger');
            return false;
        }
    });
    return true;
}
</script>