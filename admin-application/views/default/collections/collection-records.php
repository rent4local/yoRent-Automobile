<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="box--scroller">
    <p style="margin-bottom : 30px;"><span style="width:15px; height:15px; background-color: #d51414 !important;display: inline-block;margin-right: 10px;"></span><small><?php echo Labels::getLabel('Lbl_Colored_Row_is_deleted_Record.', $adminLangId)?></small></p> 
    <?php 
    if ($collectionRecords) {
        $lis = '<table class="table table-responsive table--hovered" id="collection-record"><tbody>';
        foreach ($collectionRecords as $record) {
            $productName = strip_tags(html_entity_decode($record['record_title'], ENT_QUOTES, 'UTF-8'));
            if ($collectionType == Collections::COLLECTION_TYPE_PRODUCT) {
                $options = SellerProduct::getSellerProductOptions($record['record_id'], true, $adminLangId);
                $variantsStr = '';
                array_walk($options, function ($item, $key) use (&$variantsStr) {
                    $variantsStr .= ' | ' . $item['option_name'] . ' : ' . $item['optionvalue_name'];
                });
                $userName = isset($record["credential_username"]) ? " | " . $record["credential_username"] : '';
                $productName .=  $variantsStr . $userName;
            }
            $isDeleted = (isset($record['is_deleted']) && $record['is_deleted'] == 1) ? "is-deleted" : "";
            
            
            $lis .= '<tr id="' . $record['record_id'] . '" class="'. $isDeleted .'">';
            $lis .= '<td class="dragHandle"><i class="ion-arrow-move icon"></i></id>';
            if ($collectionType == Collections::COLLECTION_TYPE_BRAND) {
                $lis .= '<td> <img src=' .  UrlHelper::generateFullUrl('Image', 'brandFeaturedImage', array($record['record_id'], 0, 'THUMB'))  . ' > </td>';
            }
            $lis .= '<td>' . $productName . '<input type="hidden" value="' . $record['record_id'] . '"  name="collection_selprod[]"></id>';
			$lis .= '<td><a class="text-dark" href="javascript:void(0)" title="Remove" onClick="removeCollectionRecord(' . $collectionId . ',' . $record['record_id'] . ');"><i class=" icon ion-close" data-record-id="' . $record['record_id'] . '"></i></a></td>';
            $lis .= '</tr>';
        }
        $lis .= '</tbody></table>';
        echo $lis;
    } ?>
</div>
<style>
    .is-deleted td{
        background-color: #d51414 !important;
        border: 1px solid transparent;
        color: var(--brand-color-inverse);
    }
</style>

<script>
    $(document).ready(function() {
        $('#collection-record').tableDnD({
            onDrop: function(table, row) {
                fcom.displayProcessing();
                var order = $.tableDnD.serialize('id');
                order += '&collection_id=<?php echo $collectionId; ?>';
                fcom.ajax(fcom.makeUrl('Collections', 'updateCollectionRecordOrder'), order, function(res) {
                    var ans = $.parseJSON(res);
                    if (ans.status == 1) {
                        fcom.displaySuccessMessage(ans.msg);
                    } else {
                        fcom.displayErrorMessage(ans.msg);
                    }
                });
            },
            dragHandle: ".dragHandle",
        });
    });
</script>