<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
$table = '';
if ($product_return_type == ProductReturns::OVERDUE_RETURN_TYPE) { 
    $table = 'table-justified';
}
?>
<table width='100%' class='table table--orders <?php echo $table;?>'>
    <thead>
        <tr>
            <th><?php echo Labels::getLabel('LBL_Sr_no.', $siteLangId); ?></th>
            <th><?php echo Labels::getLabel('LBL_Name', $siteLangId); ?></th>
            <th><?php echo Labels::getLabel('LBL_Quantity', $siteLangId); ?></th>
            <th><?php echo Labels::getLabel('LBL_Rental_end_date', $siteLangId); ?></th>
            <th><?php echo Labels::getLabel('LBL_Days_Until_Rental_End', $siteLangId); ?></th>
            <?php if ($product_return_type == ProductReturns::OVERDUE_RETURN_TYPE) { ?>
                <th class="align--center"></th>
            <?php } ?>
        </tr>
    </thead>
    <tbody>

        <?php if (!empty($arrayListing)) { ?>
            <?php
            $sr_no = ($page >= 1) ? $recordCount - (($page - 1) * $pageSize) : count($arrayListing);
            foreach ($arrayListing as $arrayList) {
                $to = date_create(date('Y-m-d'));
                $from = date_create(date("Y-m-d", strtotime($arrayList['opd_rental_end_date'])));
                $diff = date_diff($from, $to);
                $daysDiff = $diff->format('%a days');
                $orderId = $arrayList['op_order_id'];
                $oprId = $arrayList['op_id'];
                ?>
                <tr>
                    <td><?php echo $sr_no; ?></td>
                    <td>
                        <?php echo $arrayList['opr_name']; ?>
                    </td>
                    <td>
                        <?php echo $arrayList['op_qty']; ?>
                    </td>
                    <td>
                        <?php echo $arrayList['opd_rental_end_date']; ?>
                    </td>
                    <td><?php echo $daysDiff; ?></td>
                    <?php if ($product_return_type == ProductReturns::OVERDUE_RETURN_TYPE) { ?>
                        <td class="text-center">            
                            <ul class="actions">
                                <li><a href="javascript:void(0)" onclick="sendEmailForOverdueProducts('<?php echo $orderId; ?>', '<?php echo $oprId; ?>')" title="<?php echo Labels::getLabel('LBL_Send_Email', $siteLangId); ?>"><i class="fa fa-envelope"></i></a></li>
                            </ul>
                        </td>
                    <?php } ?>
                </tr>
                <?php
                $sr_no--;
            }
            ?>
        <?php } else { ?>
            <tr>
                <td colspan="5" class='empty_tr text-center'>
                    <span><?php echo Labels::getLabel('LBL_No_record_found', $siteLangId); ?></span>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<?php
$postedData['page'] = $page;
$postedData['product_return_type'] = $product_return_type;
$postedData['start_date'] = $startDate;
$postedData['end_date'] = $endDate;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmProductReturns'));
$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'callBackJsFunc' => 'goToProductReturns', 'siteLangId' => $siteLangId, 'pageSize' => $pageSize, 'removePageCentClass' => true, 'recordCount' => $recordCount);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
?>
</div>