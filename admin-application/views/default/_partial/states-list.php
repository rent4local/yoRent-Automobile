<?php defined('SYSTEM_INIT') or die('Invalid usage');
echo "<option value = '' >Select</option>";
foreach($statesArr as $id => $stateName){
	$selected = '';
	if($stateId == $id){
		$selected = 'selected';
	}
	echo "<option value='".$id."' ".$selected.">".$stateName."</option>";
}