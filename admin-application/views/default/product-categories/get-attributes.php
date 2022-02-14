<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
foreach($attributes as $grp => $data) { 
?>
<div class="group-filed">
	<h3 class="form__heading"><?php echo $grp;?></h3>
	<div class="row">
		<div class="col-md-12">
			<div class="tablewrap">
				<table class="table table-responsive table--hovered" width="100%">
					<thead>
						<tr>					
							<th width="5%"></th>
							<th width="25%"><?php echo Labels::getLabel('LBL_Field_Name', $adminLangId);?></th>
							<th width="30%"><?php echo Labels::getLabel('LBL_Field_Type', $adminLangId);?></th>
							<th width="15%"><?php echo Labels::getLabel('LBL_For_Filters', $adminLangId);?></th>
							<th width="10%"><?php echo Labels::getLabel('LBL_Postfix', $adminLangId);?></th>
							<th width="15%"></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($data as $val) { ?> 
						<tr>
							<td class="dragHandle"><i class="ion-arrow-move icon"></i></td>
							<td><?php echo $val['attr_name'];?></td>
							<td><?php echo (!empty($attrTypes[$val['attr_type']])) ? $attrTypes[$val['attr_type']] : 'N/A';?></td>
							<td><?php 
                            $yesNoArr = applicationConstants::getYesNoArr($adminLangId);
                            echo $yesNoArr[$val['attr_display_in_filter']];?></td>
							<td><?php echo $val['attr_postfix'];?></td>
							<td class="text-right">
								<a href="javascript:void(0)" class="btn btn-sm btn-clean btn-icon btn-icon-md" title="Edit" onclick="editAttr(<?php echo $val['attr_id'];?>)"><i class="fa fa-edit"></i></a>
								<a href="javascript:void(0)" class="btn btn-sm btn-clean btn-icon btn-icon-md" title="Delete" onclick="deleteAttr(<?php echo $val['attr_id'];?>)"><i class="fa fa-trash"></i></a>
							</td>
						</tr>
						<?php } ?>
					</tbody>
				</table>        
			</div>
		</div>
	</div>
</div>
<?php } ?>
