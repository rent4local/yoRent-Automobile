<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="modal-dialog modal-dialog-centered" role="document" id="admin-rules-info-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><?php echo Labels::getLabel('LBL_Admin_Rules', $siteLangId); ?></h5>

            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <?php
            $arr_flds = array(
                'listserial' => Labels::getLabel('LBL_#', $siteLangId),
                /* 'ocrule_duration_min' => Labels::getLabel('LBL_Duration_Min(In_Hours)', $siteLangId),
                'ocrule_duration_max' => Labels::getLabel('LBL_Duration_Max(In_Hours)', $siteLangId), */
                'ocrule_duration_rang' => Labels::getLabel('LBL_Duration_Range(In_Hours)', $siteLangId),
                'ocrule_refund_amount' => Labels::getLabel('LBL_Refund_Amount(In_Percentage)', $siteLangId),
            );

            $tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table splPriceList-js table-justified'));
            $th = $tbl->appendElement('thead')->appendElement('tr');
            foreach ($arr_flds as $key => $val) {
                $e = $th->appendElement('th', array(), $val);
            }
            $sr_no = 1;
            foreach ($arr_listing as $sn => $row) {
                $tr = $tbl->appendElement('tr');
                $tr->setAttribute("id", $row['ocrule_id']);
                foreach ($arr_flds as $key => $val) {
                    $td = $tr->appendElement('td');
                    switch ($key) {
                        case 'listserial':
                            $td->appendElement('plaintext', array(), $sr_no);
                            break;
                            /* case 'ocrule_duration_max':
                            if($row['ocrule_is_default'] == OrderCancelRule::MAX_VALUE){
                                $td->appendElement('plaintext', array(), "Infinity");
                            }else{
                                $td->appendElement('plaintext', array(), $row[$key]);
                            }
                            break; */
                        case 'ocrule_duration_rang':
                            if ($row['ocrule_is_default'] == OrderCancelRule::MAX_VALUE) {
                                $row['ocrule_duration_max'] = "Infinity";
                            }
                            $td->appendElement('plaintext', array(), $row['ocrule_duration_min'] . ' - ' . $row['ocrule_duration_max']);
                            break;
                        default:
                            $td->appendElement('plaintext', array(), $row[$key], true);
                            break;
                    }
                }
                $sr_no++;
            }
            echo $tbl->getHtml();
            if (count($arr_listing) == 0) {
                $message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
                $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId, 'message' => $message));
            }
            ?>
        </div>
    </div>
</div>