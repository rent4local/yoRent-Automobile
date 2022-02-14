<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
if(!empty($timeSlots)){
    foreach($timeSlots as $slot) { 
        $displayTime = date("H:i:s", strtotime('+'.$pickupInterval.' hour'));
        if($selectedDate == date('Y-m-d') && $displayTime > $slot['tslot_from_time'] && $displayTime > $slot['tslot_to_time']){
            continue;
        }
        ?>
        <li> 
            <input <?php echo ($selectedSlot == $slot['tslot_id']) ? 'checked=checked' : ''; ?> type="radio" class="control-input" name="timeSlot" id="<?php echo $slot['tslot_id'] ?>" onclick="selectTimeSlot(this, <?php echo $pickUpBy; ?>,<?php echo $selprodId;?>);">
            <label class="control-label" for="<?php echo $slot['tslot_id'] ?>">
                <span class="time"><?php echo date('H:i', strtotime($slot['tslot_from_time'])); ?> - <?php echo date('H:i', strtotime($slot['tslot_to_time'])); ?> </span>
            </label>
        </li>
        <?php
    }
} else {
    ?>
    <li>
        <label class="control-label">
            <span class="time"><?php echo Labels::getLabel('LBL_No_Time_slots_found', $siteLangId); ?></span>
        </label>
    </li>
    <?php
}
?>